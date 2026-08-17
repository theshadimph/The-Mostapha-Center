<?php

namespace App\Support\Import;

use App\Enums\ImportTargetField;

/**
 * Best-effort suggestion for which Book field an Excel column header
 * probably means, so the admin doesn't have to map all ~10 columns by hand
 * every time. Always just a starting point — the mapping step lets the
 * admin override every suggestion before anything is imported.
 */
class ColumnGuesser
{
    /**
     * @var array<string, list<string>>
     */
    private const SYNONYMS = [
        // Deliberately no bare 'الكتاب' ("the book") or 'book' — both are
        // generic enough to false-positive inside unrelated phrases like
        // "رقم الكتاب" ("book number") or "حالة الكتاب" ("book condition").
        'title' => ['عنوان', 'اسم الكتاب', 'title', 'book title'],
        'author' => ['مؤلف', 'المؤلف', 'اسم المؤلف', 'الكاتب', 'author', 'writer'],
        'editors' => ['محقق', 'المحقق', 'محققون', 'المحققون', 'editor', 'editors'],
        'commentators' => ['شارح', 'الشارح', 'شارحون', 'الشارحون', 'commentator', 'commentators'],
        'annotators' => ['محشي', 'المحشي', 'محشون', 'المحشون', 'annotator', 'annotators'],
        'curators' => ['من اعتنى بهم', 'اعتنى بهم', 'curator', 'curators'],
        'category' => ['تصنيف', 'التصنيف', 'قسم', 'القسم', 'الموضوع', 'category', 'subject', 'section'],
        'publisher' => ['ناشر', 'الناشر', 'دار النشر', 'دور النشر', 'publisher'],
        'isbn' => ['ردمك', 'الترقيم الدولي', 'isbn'],
        'publication_year' => ['سنة النشر', 'سنة الطباعة', 'سنة الاصدار', 'سنة', 'year', 'publication year'],
        'edition' => ['طبعة', 'الطبعة', 'رقم الطبعة', 'edition'],
        'pages' => ['صفحات', 'عدد الصفحات', 'pages', 'page count'],
        'language' => ['لغة', 'اللغة', 'language'],
        'description' => ['وصف', 'الوصف', 'ملاحظات', 'description', 'notes'],
        'barcode' => ['باركود', 'رقم التصنيف', 'رقم الجرد', 'barcode', 'call number', 'accession number'],
        'room_code' => ['غرفة', 'الغرفة', 'رقم الغرفة', 'room'],
        'wall_code' => ['حائط', 'الحائط', 'رقم الحائط', 'wall'],
        'section_code' => ['قاعة', 'القاعة', 'رمز القاعة', 'hall'],
        'shelf_code' => ['الرف', 'رمز الرف', 'shelf'],
        'position' => ['الموقع على الرف', 'رقم الكتاب', 'position', 'location'],
    ];

    /**
     * Plain substring matching — deliberately not word-boundary-strict,
     * because Arabic headers commonly attach the header's whole meaning to
     * a synonym via a "ال" prefix or plural suffix with no space
     * ("العنوان", "المؤلفون"), which a word-boundary check would miss
     * entirely. The trade-off is that a synonym must be long/specific
     * enough not to appear inside an unrelated word — e.g. a bare "رف"
     * ("shelf") would false-positive inside "الغرفة" ("room"), which is why
     * every synonym here is the full word ("الرف") or a multi-word phrase.
     */
    public static function guess(string $header): ?ImportTargetField
    {
        $normalized = TextNormalizer::normalize(mb_strtolower($header));

        foreach (self::SYNONYMS as $field => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (str_contains($normalized, mb_strtolower($synonym))) {
                    return ImportTargetField::from($field);
                }
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $headers
     * @return array<int, string> column index => ImportTargetField value
     */
    public static function guessAll(array $headers): array
    {
        $mapping = [];
        $used = [];

        foreach ($headers as $index => $header) {
            $guess = self::guess((string) $header);

            if ($guess !== null && ! in_array($guess->value, $used, true)) {
                $mapping[$index] = $guess->value;
                $used[] = $guess->value;
            }
        }

        return $mapping;
    }
}
