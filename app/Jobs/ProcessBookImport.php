<?php

namespace App\Jobs;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportRowErrorType;
use App\Enums\ImportTargetField;
use App\Models\Author;
use App\Models\Book;
use App\Models\ImportBatch;
use App\Models\ImportRowError;
use App\Models\LibrarySection;
use App\Support\Import\TextNormalizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Re-reads the uploaded spreadsheet and, using the column/category mapping
 * the admin already reviewed and confirmed in the wizard, creates Book
 * records in chunked transactions. One bad row never aborts the batch — it's
 * caught, logged to import_row_errors, and processing continues. Scout
 * indexing is deferred until every row is processed, then done once for the
 * whole batch (far fewer Meilisearch round-trips than indexing per row).
 */
class ProcessBookImport implements ShouldQueue
{
    use Queueable;

    /**
     * Generous ceiling for a ~10,000-row spreadsheet; the queue worker isn't
     * subject to a web request's execution-time limits.
     */
    public int $timeout = 3600;

    /**
     * Never silently retry a partially-completed import — a failure here
     * needs a human to look at the batch's report, not a blind re-run that
     * could double-import already-processed rows.
     */
    public int $tries = 1;

    private const CHUNK_SIZE = 200;

    public function __construct(public int $importBatchId) {}

    public function handle(): void
    {
        $batch = ImportBatch::findOrFail($this->importBatchId);
        $batch->update(['status' => ImportBatchStatus::Processing, 'started_at' => now()]);

        try {
            $this->process($batch);
        } catch (\Throwable $e) {
            $batch->update(['status' => ImportBatchStatus::Failed, 'completed_at' => now()]);

            throw $e;
        }
    }

    private function process(ImportBatch $batch): void
    {
        $absolutePath = Storage::disk('local')->path($batch->stored_path);
        /** @var array<int, list<mixed>> $rows */
        $rows = (Excel::toCollection(null, $absolutePath)->first() ?? collect())->toArray();
        array_shift($rows); // header row

        $batch->update(['total_rows' => count($rows)]);

        /** @var array<int, string> $columnMapping column index => ImportTargetField value */
        $columnMapping = $batch->column_mapping ?? [];
        /** @var array<string, int|null> $categoryMapping raw excel value => category_id */
        $categoryMapping = $batch->category_mapping ?? [];

        $authorCache = Author::all()->keyBy(fn (Author $a) => mb_strtolower(TextNormalizer::normalize($a->name)));

        $existingIsbns = Book::query()->whereNotNull('isbn')->pluck('isbn')
            ->map(fn ($v) => TextNormalizer::normalizeIdentifier($v))->filter()->flip()->all();
        $existingBarcodes = Book::query()->pluck('barcode')->flip()->all();
        $existingTitleKeys = Book::query()->with('author')->get()
            ->mapWithKeys(fn (Book $b) => [$this->titleKey($b->title, $b->author?->name, (string) ($b->edition ?? $b->publication_year ?? '')) => true])
            ->all();

        $importedIds = [];
        $rowNumber = 1; // header was row 1

        // Disabled once for the whole batch, not per row — each Book::create()
        // below would otherwise queue an individual Meilisearch sync call.
        Book::withoutSyncingToSearch(function () use (
            $rows, $columnMapping, $categoryMapping, $authorCache,
            &$existingIsbns, &$existingBarcodes, &$existingTitleKeys,
            &$importedIds, &$rowNumber, $batch
        ) {
            foreach (array_chunk($rows, self::CHUNK_SIZE, true) as $chunk) {
                $counters = [
                    'imported_count' => 0, 'duplicate_count' => 0, 'validation_failed_count' => 0,
                    'unknown_category_count' => 0, 'unknown_author_count' => 0, 'error_count' => 0,
                ];

                DB::transaction(function () use (
                    $chunk, $columnMapping, $categoryMapping, $authorCache,
                    &$existingIsbns, &$existingBarcodes, &$existingTitleKeys,
                    &$importedIds, &$rowNumber, &$counters, $batch
                ) {
                    foreach ($chunk as $row) {
                        $rowNumber++;

                        try {
                            $this->processRow(
                                $row, $columnMapping, $categoryMapping, $authorCache,
                                $existingIsbns, $existingBarcodes, $existingTitleKeys,
                                $importedIds, $rowNumber, $counters, $batch,
                            );
                        } catch (\Throwable $e) {
                            $this->logRowError($batch, $rowNumber, ImportRowErrorType::Error, $e->getMessage(), $this->extractFields($row, $columnMapping));
                            $counters['error_count']++;
                        }
                    }
                });

                $batch->increment('processed_rows', count($chunk));
                foreach ($counters as $column => $amount) {
                    if ($amount > 0) {
                        $batch->increment($column, $amount);
                    }
                }
            }
        });

        if ($importedIds !== []) {
            // Calling the trait's real batching method directly (rather than
            // the `->searchable()` query-builder macro Scout registers at
            // runtime) so this stays statically analyzable — it's the same
            // underlying sync Scout's own `Model::makeAllSearchable()` uses.
            (new Book)->queueMakeSearchable(Book::query()->whereIn('id', $importedIds)->get());
        }

        $batch->update(['status' => ImportBatchStatus::Completed, 'completed_at' => now()]);
    }

    /**
     * @param  list<mixed>  $row
     * @param  array<int, string>  $columnMapping
     * @param  array<string, int|null>  $categoryMapping
     * @param  Collection<string, Author>  $authorCache
     * @param  array<string, bool>  $existingIsbns
     * @param  array<string, bool>  $existingBarcodes
     * @param  array<string, bool>  $existingTitleKeys
     * @param  list<int>  $importedIds
     * @param  array<string, int>  $counters
     */
    private function processRow(
        array $row,
        array $columnMapping,
        array $categoryMapping,
        Collection $authorCache,
        array &$existingIsbns,
        array &$existingBarcodes,
        array &$existingTitleKeys,
        array &$importedIds,
        int $rowNumber,
        array &$counters,
        ImportBatch $batch,
    ): void {
        $data = $this->extractFields($row, $columnMapping);

        // Silently ignore fully blank rows (common spacer rows in real
        // library spreadsheets) — not worth a validation-error entry.
        if (collect($data)->filter(fn ($v) => $v !== null)->isEmpty()) {
            return;
        }

        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'pages' => ['nullable', 'integer', 'min:1', 'max:20000'],
        ]);

        if ($validator->fails()) {
            $this->logRowError($batch, $rowNumber, ImportRowErrorType::Validation, $validator->errors()->first(), $data);
            $counters['validation_failed_count']++;

            return;
        }

        $isbnKey = TextNormalizer::normalizeIdentifier($data['isbn']);
        $barcodeKey = TextNormalizer::normalizeIdentifier($data['barcode']);
        $titleKey = $this->titleKey($data['title'], $data['author'], $data['edition'] ?? $data['publication_year'] ?? '');

        $isDuplicate = ($isbnKey !== '' && isset($existingIsbns[$isbnKey]))
            || ($barcodeKey !== '' && isset($existingBarcodes[$barcodeKey]))
            || ($isbnKey === '' && $barcodeKey === '' && isset($existingTitleKeys[$titleKey]));

        if ($isDuplicate) {
            $this->logRowError($batch, $rowNumber, ImportRowErrorType::Duplicate, __('كتاب مكرر (تم العثور على تطابق بالردمك أو الباركود أو العنوان والمؤلف والطبعة).'), $data);
            $counters['duplicate_count']++;

            return;
        }

        // Author: exact normalized-name match only — never a fuzzy merge of
        // two possibly-different people. A never-seen name becomes a new
        // Author record, which is always a safe outcome.
        $authorId = null;
        if ($data['author'] !== null) {
            $key = mb_strtolower(TextNormalizer::normalize($data['author']));
            $author = $authorCache->get($key);

            if (! $author) {
                $author = Author::create(['name' => $data['author']]);
                $authorCache->put($key, $author);
            }

            $authorId = $author->id;
        } else {
            $counters['unknown_author_count']++;
        }

        // Category: resolved purely from the admin-approved mapping built in
        // the wizard — the row-processing job never creates or guesses a
        // category on its own.
        $categoryId = null;
        if ($data['category'] === null) {
            $counters['unknown_category_count']++;
        } elseif (array_key_exists($data['category'], $categoryMapping) && $categoryMapping[$data['category']] !== null) {
            $categoryId = $categoryMapping[$data['category']];
        } else {
            $counters['unknown_category_count']++;
        }

        $shelfId = null;
        $position = null;
        $sectionCode = $data['section_code'] ?? $this->combineRoomAndWall($data['room_code'], $data['wall_code']);

        if ($sectionCode !== null && $data['shelf_code'] !== null) {
            $section = LibrarySection::query()->firstOrCreate(
                ['code' => $sectionCode],
                ['name' => $data['section_code'] ?? $this->roomAndWallName($data['room_code'], $data['wall_code'])],
            );
            $shelf = $section->shelves()->firstOrCreate(['code' => $data['shelf_code']]);
            $shelfId = $shelf->id;
            $position = $data['position'];
        }

        $book = Book::create([
            'title' => $data['title'],
            'author_id' => $authorId,
            'editors' => $this->splitNames($data['editors']),
            'commentators' => $this->splitNames($data['commentators']),
            'annotators' => $this->splitNames($data['annotators']),
            'curators' => $this->splitNames($data['curators']),
            'category_id' => $categoryId,
            'description' => $data['description'],
            'publisher' => $data['publisher'],
            'publication_year' => $data['publication_year'] !== null ? (int) $data['publication_year'] : null,
            'edition' => $data['edition'],
            'isbn' => $data['isbn'],
            'pages' => $data['pages'] !== null ? (int) $data['pages'] : null,
            'language' => $data['language'] ?? 'ar',
            'barcode' => $data['barcode'],
            'shelf_id' => $shelfId,
            'position' => $position,
        ]);

        $importedIds[] = $book->id;
        $counters['imported_count']++;

        if ($isbnKey !== '') {
            $existingIsbns[$isbnKey] = true;
        }
        $existingBarcodes[TextNormalizer::normalizeIdentifier($book->barcode)] = true;
        $existingTitleKeys[$titleKey] = true;
    }

    /**
     * @param  list<mixed>  $row
     * @param  array<int, string>  $columnMapping
     * @return array<string, string|null>
     */
    private function extractFields(array $row, array $columnMapping): array
    {
        $data = array_fill_keys(array_map(fn ($f) => $f->value, ImportTargetField::all()), null);

        foreach ($columnMapping as $index => $fieldValue) {
            $data[$fieldValue] = $this->cleanCellValue($row[$index] ?? null);
        }

        return $data;
    }

    private function cleanCellValue(mixed $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        if (is_float($raw) && floor($raw) === $raw) {
            $raw = (string) (int) $raw;
        }

        $normalized = TextNormalizer::normalize((string) $raw);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * Splits a cell like "أحمد الأرنؤوط, محمد بوينوكالن" into individual
     * names for the editors/commentators/annotators/curators JSON columns.
     *
     * @return list<string>|null
     */
    private function splitNames(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $names = array_values(array_filter(
            array_map(fn (string $name) => trim($name), preg_split('/[,،]/u', $value) ?: []),
            fn (string $name) => $name !== '',
        ));

        return $names !== [] ? $names : null;
    }

    /**
     * The file separates a book's physical location into room + wall +
     * shelf + book-number, one level deeper than this app's section/shelf
     * model. When there's no single mapped section_code column, the room
     * and wall values are combined into one section code instead of adding
     * a fourth location table.
     */
    private function combineRoomAndWall(?string $room, ?string $wall): ?string
    {
        if ($room === null && $wall === null) {
            return null;
        }

        return trim(implode('-', array_filter([$room, $wall], fn ($v) => $v !== null)), '-');
    }

    private function roomAndWallName(?string $room, ?string $wall): string
    {
        $parts = array_filter([
            $room !== null ? (string) __('غرفة :n', ['n' => $room]) : null,
            $wall !== null ? (string) __('حائط :n', ['n' => $wall]) : null,
        ]);

        return implode(' - ', $parts);
    }

    private function titleKey(?string $title, ?string $author, string $editionOrYear): string
    {
        return mb_strtolower(TextNormalizer::normalize($title ?? '')).'|'
            .mb_strtolower(TextNormalizer::normalize($author ?? '')).'|'
            .mb_strtolower(TextNormalizer::normalize($editionOrYear));
    }

    /**
     * @param  array<string, string|null>  $rowData
     */
    private function logRowError(ImportBatch $batch, int $rowNumber, ImportRowErrorType $type, string $message, array $rowData): void
    {
        ImportRowError::create([
            'import_batch_id' => $batch->id,
            'row_number' => $rowNumber,
            'type' => $type,
            'message' => $message,
            'row_data' => $rowData,
        ]);
    }
}
