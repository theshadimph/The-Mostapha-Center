<?php

namespace App\Enums;

enum BookStatus: string
{
    case Available = 'available';
    case Borrowed = 'borrowed';
    case ReferenceOnly = 'reference_only';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('متوفر'),
            self::Borrowed => __('مستعار'),
            self::ReferenceOnly => __('للاطلاع داخل المكتبة فقط'),
            self::Unavailable => __('غير متوفر'),
        };
    }

    /**
     * Whether a visitor can actually find this book on the shelf right now.
     */
    public function isPubliclyAvailable(): bool
    {
        return $this === self::Available || $this === self::ReferenceOnly;
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::ReferenceOnly => 'primary',
            self::Borrowed => 'warning',
            self::Unavailable => 'neutral',
        };
    }

    /**
     * @return array<int, self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
