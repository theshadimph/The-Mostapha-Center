<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LibrarySection;
use App\Models\User;

class LibrarySectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function update(User $user, LibrarySection $librarySection): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }

    public function delete(User $user, LibrarySection $librarySection): bool
    {
        return $user->hasRole(...UserRole::libraryManagers());
    }
}
