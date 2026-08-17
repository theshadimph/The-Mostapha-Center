<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthorizationSweepTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminRoutes(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'books' => ['admin.books.index'],
            'authors' => ['admin.authors.index'],
            'categories' => ['admin.categories.index'],
            'sections' => ['admin.sections.index'],
            'import' => ['admin.import.index'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_guests_are_redirected_to_login_for_every_admin_route(string $routeName): void
    {
        $this->get(route($routeName))->assertRedirect(route('login'));
    }

    #[DataProvider('adminRoutes')]
    public function test_plain_registered_users_are_forbidden_from_every_admin_route(string $routeName): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route($routeName))->assertForbidden();
    }

    #[DataProvider('adminRoutes')]
    public function test_editor_role_is_forbidden_from_catalog_admin_routes(string $routeName): void
    {
        $editor = User::factory()->role(UserRole::Editor)->create();

        $response = $this->actingAs($editor)->get(route($routeName));

        // Editors may reach the dashboard (any staff role can) but not the
        // catalog-management screens, which are librarian/super-admin only.
        if ($routeName === 'dashboard') {
            $response->assertOk();
        } else {
            $response->assertForbidden();
        }
    }

    public function test_only_super_admin_reaches_user_management(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $superAdmin = User::factory()->role(UserRole::SuperAdmin)->create();

        $this->actingAs($librarian)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_librarian_and_super_admin_reach_catalog_routes(): void
    {
        foreach ([UserRole::Librarian, UserRole::SuperAdmin] as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)->get(route('admin.books.index'))->assertOk();
            $this->actingAs($user)->get(route('admin.authors.index'))->assertOk();
            $this->actingAs($user)->get(route('admin.categories.index'))->assertOk();
            $this->actingAs($user)->get(route('admin.sections.index'))->assertOk();
            $this->actingAs($user)->get(route('admin.import.index'))->assertOk();
        }
    }
}
