<?php

namespace App\Enums;

enum ImportRowErrorType: string
{
    case Validation = 'validation';
    case Duplicate = 'duplicate';
    case UnknownCategory = 'unknown_category';
    case UnknownAuthor = 'unknown_author';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Validation => __('خطأ في البيانات'),
            self::Duplicate => __('كتاب مكرر'),
            self::UnknownCategory => __('بدون تصنيف'),
            self::UnknownAuthor => __('بدون مؤلف'),
            self::Error => __('خطأ آخر'),
        };
    }

    /**
     * Validation/duplicate/unexpected-error rows are rejected outright.
     * UnknownCategory/UnknownAuthor are informational notices only — the
     * book is still imported, just without that relation set.
     */
    public function blocksImport(): bool
    {
        return match ($this) {
            self::Validation, self::Duplicate, self::Error => true,
            self::UnknownCategory, self::UnknownAuthor => false,
        };
    }
}
