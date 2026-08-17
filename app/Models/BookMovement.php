<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'book_id',
        'from_shelf_id',
        'from_position',
        'to_shelf_id',
        'to_position',
        'moved_by',
        'note',
    ];

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * @return BelongsTo<Shelf, $this>
     */
    public function fromShelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'from_shelf_id');
    }

    /**
     * @return BelongsTo<Shelf, $this>
     */
    public function toShelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'to_shelf_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
