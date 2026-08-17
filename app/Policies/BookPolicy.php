<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function view(User $user, Book $book): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function update(User $user, Book $book): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    /**
     * Relocating a book to a new shelf/position is the same privilege as
     * editing it — kept as a distinct ability name for clarity at call sites.
     */
    public function move(User $user, Book $book): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }
}
