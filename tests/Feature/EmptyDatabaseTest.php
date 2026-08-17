<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The default `php artisan migrate --seed` path (see DatabaseSeeder) now
 * bootstraps only the Super Admin — no categories, sections, authors, or
 * books. Every page that assumes data exists must degrade to a real empty
 * state instead of crashing.
 */
class EmptyDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_app_works_with_only_the_seeded_super_admin(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->firstOrFail();
        $this->assertTrue($admin->isSuperAdmin());

        $this->get(route('home'))->assertOk()->assertSee('لا توجد كتب متاحة حالياً');
        $this->get(route('search'))->assertOk();

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('لا توجد كتب بعد');
        $this->actingAs($admin)->get(route('admin.books.index'))->assertOk()->assertSee('لا توجد كتب مطابقة');
        $this->actingAs($admin)->get(route('admin.authors.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk()->assertSee('لا توجد أقسام بعد');
        $this->actingAs($admin)->get(route('admin.sections.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.import.index'))->assertOk()->assertSee('لا توجد عمليات استيراد بعد');
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee($admin->name);
    }
}
