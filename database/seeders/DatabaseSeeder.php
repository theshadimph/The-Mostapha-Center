<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * By default this only bootstraps the first Super Admin account — a
     * real deployment starts with an empty catalog and imports the center's
     * actual ~10,000-book library via /admin/import, not invented reference
     * data. CategorySeeder and LibrarySectionSeeder still exist and are
     * production-safe to run, but are opt-in (`php artisan db:seed
     * --class=CategorySeeder`) for anyone who wants a quick starter taxonomy
     * before importing real data, rather than something every install gets
     * whether it matches their real categories or not.
     *
     * The demo book catalog (and everything it depends on) is local-only so
     * production/staging never gets seeded with invented books.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        if (app()->environment('local')) {
            $this->call([
                CategorySeeder::class,
                LibrarySectionSeeder::class,
            ]);

            User::factory()->librarian()->create([
                'name' => 'أمين المكتبة',
                'email' => 'librarian@mostapha-center.test',
            ]);

            $this->call(DemoBooksSeeder::class);
        }
    }
}
