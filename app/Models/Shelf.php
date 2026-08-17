<?php

namespace App\Models;

use Database\Factories\ShelfFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelf extends Model
{
    /** @use HasFactory<ShelfFactory> */
    use HasFactory;

    protected $fillable = ['library_section_id', 'code', 'description'];

    /**
     * @return BelongsTo<LibrarySection, $this>
     */
    public function librarySection(): BelongsTo
    {
        return $this->belongsTo(LibrarySection::class);
    }

    /**
     * @return HasMany<Book, $this>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Human-readable location prefix for this shelf, e.g. "F03-02".
     * Combined with a book's position it forms the full location code.
     */
    public function codePrefix(): string
    {
        return "{$this->librarySection->code}-{$this->code}";
    }
}
