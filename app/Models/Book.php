<?php

namespace App\Models;

use App\Enums\BookStatus;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

/**
 * @property-read BookStatus $status Larastan can't resolve this enum cast from
 *   casts() on its own for this particular column (confirmed independent of
 *   Scout/Searchable — reproduces on a clean model with no other traits, and
 *   only for this one property; `User::$role`'s identical UserRole cast
 *   resolves correctly). Runtime behavior is correct (proven by the full
 *   test suite); this is a declared correction of Larastan's own inference,
 *   not a suppression of a real error.
 * @property-read array<int, string>|null $editors Names of people who verified/edited the text (محققون).
 * @property-read array<int, string>|null $commentators Names of people who wrote commentary (شارحون).
 * @property-read array<int, string>|null $annotators Names of people who wrote annotations (محشّون).
 * @property-read array<int, string>|null $curators Names of people who prepared/took care of the text (من اعتنى بهم).
 */
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'barcode',
        'title',
        'slug',
        'author_id',
        'editors',
        'commentators',
        'annotators',
        'curators',
        'category_id',
        'description',
        'publisher',
        'publication_year',
        'edition',
        'isbn',
        'pages',
        'language',
        'cover_image',
        'status',
        'shelf_id',
        'position',
        'views_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookStatus::class,
            'publication_year' => 'integer',
            'pages' => 'integer',
            'views_count' => 'integer',
            'editors' => 'array',
            'commentators' => 'array',
            'annotators' => 'array',
            'curators' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Author, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Shelf, $this>
     */
    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    /**
     * @return HasMany<BookMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(BookMovement::class)->latest('created_at');
    }

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            $book->slug ??= self::uniqueSlug($book->title);
            $book->barcode ??= self::generateBarcode();
        });
    }

    /**
     * Arabic titles don't survive ASCII transliteration, so slugs keep the
     * original Unicode text (language=null skips Str::slug's ascii step).
     */
    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title, '-', null) ?: Str::random(8);
        $slug = $base;
        $suffix = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Stable identifier for QR/barcode labels, independent of the book's
     * physical location so moving a book never changes its identity.
     */
    public static function generateBarcode(): string
    {
        do {
            $candidate = 'MC-'.strtoupper(Str::random(8));
        } while (self::where('barcode', $candidate)->exists());

        return $candidate;
    }

    /**
     * Human-readable physical location code, e.g. "F03-02-45", or null if unshelved.
     */
    public function getLocationCodeAttribute(): ?string
    {
        if (! $this->shelf || $this->position === null) {
            return null;
        }

        return "{$this->shelf->codePrefix()}-{$this->position}";
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    public function isShelved(): bool
    {
        return $this->shelf_id !== null && $this->position !== null;
    }

    /**
     * The Scout/Meilisearch index name (see config/scout.php's
     * meilisearch.index-settings.books for filterable/sortable attributes).
     */
    public function searchableAs(): string
    {
        return 'books';
    }

    /**
     * Fields sent to the search index. author/category are flattened to
     * their name (searchable text) plus category_id kept raw (filterable,
     * used by SearchController's category dropdown).
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author?->name,
            'category' => $this->category?->name,
            'category_id' => $this->category_id,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'status' => $this->status->value,
            // Space-joined rather than a raw array: keeps every searchable
            // value a plain scalar (matching author/category above), which
            // also sidesteps Scout's `collection` test driver JSON-encoding
            // non-scalars — PHP's default json_encode escapes Arabic to
            // \uXXXX, which would silently break substring search in tests
            // even though Meilisearch itself handles raw arrays fine.
            'editors' => $this->editors ? implode(' ', $this->editors) : null,
            'commentators' => $this->commentators ? implode(' ', $this->commentators) : null,
            'annotators' => $this->annotators ? implode(' ', $this->annotators) : null,
            'curators' => $this->curators ? implode(' ', $this->curators) : null,
        ];
    }

    /**
     * Eager-load the relations toSearchableArray() reads, so bulk indexing
     * (`php artisan scout:import`) doesn't N+1 over ~10,000 books.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['author', 'category']);
    }

    /**
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeInCategory(Builder $query, Category|string|null $category): Builder
    {
        if (! $category) {
            return $query;
        }

        return $query->whereHas(
            'category',
            fn (Builder $q) => $q->where('slug', $category instanceof Category ? $category->slug : $category),
        );
    }
}
