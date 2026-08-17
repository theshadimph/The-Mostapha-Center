<?php

namespace App\Models;

use App\Enums\ImportRowErrorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read ImportRowErrorType $type Same Larastan enum-cast inference
 *   gap documented on Book::$status — the cast is correct at runtime.
 * @property-read array<string, string|null> $row_data
 */
class ImportRowError extends Model
{
    protected $fillable = [
        'import_batch_id',
        'row_number',
        'type',
        'message',
        'row_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ImportRowErrorType::class,
            'row_number' => 'integer',
            'row_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<ImportBatch, $this>
     */
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }
}
