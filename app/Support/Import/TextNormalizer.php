<?php

namespace App\Support\Import;

/**
 * A single, conservative normalization used everywhere the importer needs to
 * compare two strings for "is this the same value" (author de-duplication,
 * duplicate-book title matching, ISBN matching). Deliberately limited to
 * whitespace/punctuation cleanup — never a fuzzy/phonetic match — so it can
 * never silently merge two genuinely different authors or books.
 */
class TextNormalizer
{
    public static function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Collapse repeated whitespace (including Arabic tatweel/kashida used
        // as visual padding in some library spreadsheets) and trim.
        $value = str_replace("\u{0640}", '', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * Digits-only comparison key for ISBN/barcode matching — tolerant of
     * dashes/spaces formatting differences, never of actual digit changes.
     */
    public static function normalizeIdentifier(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/[^0-9Xx]/', '', $value) ?? '';
    }
}
