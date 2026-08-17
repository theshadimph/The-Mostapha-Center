<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'parent_id'];

    /**
     * @return HasMany<Book, $this>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            $category->slug ??= self::uniqueSlug($category->name);
        });
    }

    /**
     * Arabic titles don't survive ASCII transliteration (Str::slug's default
     * "language" pass would strip the script entirely), so slugs are built
     * from the original Unicode text — language=null skips that step and
     * keeps any \pL letters, Arabic included.
     */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-', null) ?: Str::random(8);
        $slug = $base;
        $suffix = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
