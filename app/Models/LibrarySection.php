<?php

namespace App\Models;

use Database\Factories\LibrarySectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LibrarySection extends Model
{
    /** @use HasFactory<LibrarySectionFactory> */
    use HasFactory;

    protected $fillable = ['name', 'code', 'floor', 'description'];

    /**
     * @return HasMany<Shelf, $this>
     */
    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class);
    }

    /**
     * @return HasManyThrough<Book, Shelf, $this>
     */
    public function books(): HasManyThrough
    {
        return $this->hasManyThrough(Book::class, Shelf::class);
    }
}
