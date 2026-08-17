<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Librarian = 'librarian';
    case Editor = 'editor';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('مدير عام'),
            self::Librarian => __('أمين مكتبة'),
            self::Editor => __('محرر محتوى'),
            self::Staff => __('موظف'),
        };
    }

    /**
     * Roles that may manage the book catalog (books, authors, categories, locations).
     *
     * @return array<int, self>
     */
    public static function libraryManagers(): array
    {
        return [self::SuperAdmin, self::Librarian];
    }

    /**
     * Roles that may manage public content pages.
     *
     * @return array<int, self>
     */
    public static function contentManagers(): array
    {
        return [self::SuperAdmin, self::Editor];
    }

    /**
     * @return array<int, self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
