<?php

namespace Tests\Feature\Admin;

use App\Enums\ImportBatchStatus;
use App\Enums\UserRole;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BookImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /**
     * Builds a real .xlsx fixture (not a mock) so the test exercises the
     * actual PhpSpreadsheet-backed parsing path, not just the surrounding
     * plumbing.
     *
     * @param  list<string>  $headers
     * @param  list<list<mixed>>  $rows
     */
    private function makeUploadedSpreadsheet(array $headers, array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $tmpPath = tempnam(sys_get_temp_dir(), 'import-test').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);

        $uploaded = UploadedFile::fake()->createWithContent('books.xlsx', file_get_contents($tmpPath));
        unlink($tmpPath);

        return $uploaded;
    }

    public function test_guests_cannot_access_the_import_page(): void
    {
        $this->get(route('admin.import.index'))->assertRedirect(route('login'));
    }

    public function test_non_privileged_users_cannot_access_the_import_page(): void
    {
        $staff = User::factory()->role(UserRole::Staff)->create();

        $this->actingAs($staff)->get(route('admin.import.index'))->assertForbidden();
    }

    public function test_guests_cannot_upload_a_spreadsheet(): void
    {
        $file = $this->makeUploadedSpreadsheet(['العنوان'], [['كتاب']]);

        $this->post(route('admin.import.store'), ['file' => $file])->assertRedirect(route('login'));
        $this->assertSame(0, ImportBatch::count());
    }

    public function test_non_privileged_users_cannot_upload_a_spreadsheet(): void
    {
        $staff = User::factory()->role(UserRole::Staff)->create();
        $file = $this->makeUploadedSpreadsheet(['العنوان'], [['كتاب']]);

        $this->actingAs($staff)->post(route('admin.import.store'), ['file' => $file])->assertForbidden();
        $this->assertSame(0, ImportBatch::count());
    }

    public function test_upload_rejects_a_non_spreadsheet_file(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        $this->actingAs($librarian)
            ->post(route('admin.import.store'), ['file' => UploadedFile::fake()->create('not-a-spreadsheet.pdf', 100)])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, ImportBatch::count());
    }

    public function test_librarian_can_upload_a_spreadsheet_and_starts_at_column_mapping(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        $file = $this->makeUploadedSpreadsheet(
            ['العنوان', 'المؤلف'],
            [['كتاب تجريبي', 'مؤلف تجريبي']],
        );

        $this->actingAs($librarian)
            ->post(route('admin.import.store'), ['file' => $file])
            ->assertRedirect();

        $batch = ImportBatch::firstOrFail();
        $this->assertSame(ImportBatchStatus::Uploaded, $batch->status);
        $this->assertSame($librarian->id, $batch->user_id);

        Livewire::actingAs($librarian)
            ->test('pages::admin.import.show', ['importBatch' => $batch])
            ->assertSee('العنوان')
            ->assertSee('المؤلف');
    }

    public function test_column_mapping_requires_a_title_column(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $batch = ImportBatch::create([
            'user_id' => $librarian->id,
            'original_filename' => 'books.xlsx',
            'stored_path' => $this->storeFixture(['العنوان'], [['كتاب']]),
            'status' => ImportBatchStatus::Uploaded,
        ]);

        Livewire::actingAs($librarian)
            ->test('pages::admin.import.show', ['importBatch' => $batch])
            ->set('columnMapping.0', '')
            ->call('saveColumnMapping')
            ->assertHasErrors(['columnMapping']);

        $this->assertSame(ImportBatchStatus::Uploaded, $batch->fresh()->status);
    }

    private function storeFixture(array $headers, array $rows): string
    {
        $file = $this->makeUploadedSpreadsheet($headers, $rows);

        return $file->store('imports', 'local');
    }

    public function test_full_import_pipeline_maps_categories_deduplicates_authors_and_detects_duplicates(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        $existingCategory = Category::factory()->create(['name' => 'الفقه الإسلامي']);
        $existingAuthor = Author::factory()->create(['name' => 'أحمد الشيخ']);
        Book::factory()->create([
            'title' => 'كتاب قديم',
            'isbn' => '978-1111111111',
            'author_id' => $existingAuthor->id,
            'category_id' => $existingCategory->id,
        ]);

        $headers = ['العنوان', 'المؤلف', 'التصنيف', 'الردمك', 'سنة النشر'];
        $rows = [
            // A: valid, maps to the existing category + existing author.
            ['كتاب الفقه الميسر', 'أحمد الشيخ', 'الفقه الإسلامي', '978-2222222222', 2020],
            // B: duplicate — same ISBN as the pre-existing book.
            ['كتاب آخر بنفس الردمك', 'مؤلف آخر', 'الفقه الإسلامي', '978-1111111111', 2019],
            // C: invalid — blank title.
            ['', 'مؤلف بلا عنوان', 'الفقه الإسلامي', '978-4444444444', 2018],
            // D: new category + author name with extra whitespace (must reuse the same Author, not duplicate it).
            ['كتاب اللغة العربية', '  أحمد الشيخ  ', 'قسم جديد تماماً', '978-3333333333', 2021],
        ];

        $batch = ImportBatch::create([
            'user_id' => $librarian->id,
            'original_filename' => 'books.xlsx',
            'stored_path' => $this->storeFixture($headers, $rows),
            'status' => ImportBatchStatus::Uploaded,
        ]);

        $component = Livewire::actingAs($librarian)->test('pages::admin.import.show', ['importBatch' => $batch]);

        $component
            ->set('columnMapping.0', 'title')
            ->set('columnMapping.1', 'author')
            ->set('columnMapping.2', 'category')
            ->set('columnMapping.3', 'isbn')
            ->set('columnMapping.4', 'publication_year')
            ->call('saveColumnMapping')
            ->assertHasNoErrors();

        $batch->refresh();
        $this->assertSame(ImportBatchStatus::Mapping, $batch->status);

        // Re-mount so the component rebuilds categoryDecisions from the saved mapping.
        $component = Livewire::actingAs($librarian)->test('pages::admin.import.show', ['importBatch' => $batch]);

        $component
            ->set('categoryDecisions.الفقه الإسلامي.mode', 'map')
            ->set('categoryDecisions.الفقه الإسلامي.category_id', $existingCategory->id)
            ->set('categoryDecisions.قسم جديد تماماً.mode', 'create')
            ->set('categoryDecisions.قسم جديد تماماً.name', 'قسم جديد تماماً')
            ->call('saveCategoryMapping')
            ->assertHasNoErrors();

        $batch->refresh();
        $this->assertSame(ImportBatchStatus::Ready, $batch->status);

        $newCategory = Category::where('name', 'قسم جديد تماماً')->firstOrFail();

        Livewire::actingAs($librarian)
            ->test('pages::admin.import.show', ['importBatch' => $batch])
            ->call('startImport');

        $batch->refresh();

        $this->assertSame(ImportBatchStatus::Completed, $batch->status);
        $this->assertSame(4, $batch->total_rows);
        $this->assertSame(2, $batch->imported_count);
        $this->assertSame(1, $batch->duplicate_count);
        $this->assertSame(1, $batch->validation_failed_count);

        $bookA = Book::where('title', 'كتاب الفقه الميسر')->firstOrFail();
        $this->assertSame($existingCategory->id, $bookA->category_id);
        $this->assertSame($existingAuthor->id, $bookA->author_id);

        $bookD = Book::where('title', 'كتاب اللغة العربية')->firstOrFail();
        $this->assertSame($newCategory->id, $bookD->category_id);
        $this->assertSame($existingAuthor->id, $bookD->author_id, 'Whitespace-padded author name must reuse the existing Author, not duplicate it.');

        $this->assertSame(1, Author::where('name', 'LIKE', '%أحمد الشيخ%')->count());
        $this->assertDatabaseMissing('books', ['title' => '']);

        // The imported book must be indexed and findable via Scout (collection driver in tests).
        $this->assertTrue(Book::search('الميسر')->get()->contains('id', $bookA->id));
    }
}
