<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Bootstraps the first super-admin account so someone can log in and
     * start managing the catalog. Credentials come from the environment so
     * production deployments don't ship a known default password.
     *
     * Safe to run repeatedly: updates the role/password of an existing user
     * with the same email rather than duplicating it.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('app.admin.email')],
            [
                'name' => config('app.admin.name'),
                'password' => config('app.admin.password'),
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
            ],
        );
    }
}
