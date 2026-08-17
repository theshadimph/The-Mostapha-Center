<?php

namespace App\Models;

use App\Enums\ImportBatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read ImportBatchStatus $status Same Larastan enum-cast inference
 *   gap documented on Book::$status — the cast is correct at runtime.
 * @property-read array<int, string>|null $column_mapping
 * @property-read array<string, int|null>|null $category_mapping
 */
class ImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'original_filename',
        'stored_path',
        'status',
        'column_mapping',
        'category_mapping',
        'total_rows',
        'processed_rows',
        'imported_count',
        'duplicate_count',
        'validation_failed_count',
        'unknown_category_count',
        'unknown_author_count',
        'error_count',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ImportBatchStatus::class,
            'column_mapping' => 'array',
            'category_mapping' => 'array',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'imported_count' => 'integer',
            'duplicate_count' => 'integer',
            'validation_failed_count' => 'integer',
            'unknown_category_count' => 'integer',
            'unknown_author_count' => 'integer',
            'error_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ImportRowError, $this>
     */
    public function rowErrors(): HasMany
    {
        return $this->hasMany(ImportRowError::class);
    }
}
