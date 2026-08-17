<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function update(User $user, Author $author): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function delete(User $user, Author $author): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }
}
