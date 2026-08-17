<?php

namespace App\Http\Responses\Concerns;

use Illuminate\Http\Request;

trait RedirectsBasedOnRole
{
    /**
     * Staff (any non-null role) land on the admin dashboard; everyone else
     * (public visitors with a plain account) lands on the public homepage.
     */
    protected function defaultRedirectPath(Request $request): string
    {
        return $request->user()?->isStaff()
            ? route('dashboard', absolute: false)
            : route('home', absolute: false);
    }
}
