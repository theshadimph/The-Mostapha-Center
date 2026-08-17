<?php

namespace App\Actions\Library;

use App\Models\Book;
use App\Models\BookMovement;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MoveBookToLocation
{
    /**
     * Relocate a book to a new shelf/position, recording the move so staff
     * can always answer "where is this now" and "where was it before".
     *
     * Pass a null $shelf to unshelve the book (e.g. pulled for repair).
     */
    public function handle(Book $book, ?Shelf $shelf, ?string $position, ?User $movedBy = null, ?string $note = null): BookMovement
    {
        if ($shelf && $position !== null) {
            $this->ensurePositionIsFree($book, $shelf, $position);
        }

        return DB::transaction(function () use ($book, $shelf, $position, $movedBy, $note) {
            $movement = BookMovement::create([
                'book_id' => $book->id,
                'from_shelf_id' => $book->shelf_id,
                'from_position' => $book->position,
                'to_shelf_id' => $shelf?->id,
                'to_position' => $shelf ? $position : null,
                'moved_by' => $movedBy?->id,
                'note' => $note,
            ]);

            $book->update([
                'shelf_id' => $shelf?->id,
                'position' => $shelf ? $position : null,
            ]);

            return $movement;
        });
    }

    protected function ensurePositionIsFree(Book $book, Shelf $shelf, string $position): void
    {
        $taken = Book::query()
            ->where('shelf_id', $shelf->id)
            ->where('position', $position)
            ->where('id', '!=', $book->id)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'position' => __('هذا الموقع مشغول بكتاب آخر بالفعل.'),
            ]);
        }
    }
}
