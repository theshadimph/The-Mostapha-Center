<?php

namespace App\Enums;

enum ImportBatchStatus: string
{
    case Uploaded = 'uploaded';
    case Mapping = 'mapping';
    case Ready = 'ready';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => __('تم الرفع'),
            self::Mapping => __('جارٍ تحديد الأعمدة'),
            self::Ready => __('جاهز للاستيراد'),
            self::Processing => __('جارٍ الاستيراد'),
            self::Completed => __('اكتمل'),
            self::Failed => __('فشل'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Completed => 'success',
            self::Failed => 'error',
            self::Processing => 'warning',
            default => 'neutral',
        };
    }
}
