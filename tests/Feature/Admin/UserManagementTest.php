<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_user_management(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        $this->actingAs($librarian)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_can_grant_a_role(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();
        $plainUser = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('updateRole', $plainUser->id, UserRole::Librarian->value);

        $this->assertSame(UserRole::Librarian, $plainUser->fresh()->role);
    }

    public function test_super_admin_can_revoke_a_role(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('updateRole', $librarian->id, '');

        $this->assertNull($librarian->fresh()->role);
    }

    public function test_super_admin_cannot_remove_their_own_role(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('updateRole', $admin->id, '');

        $this->assertSame(UserRole::SuperAdmin, $admin->fresh()->role);
    }

    public function test_super_admin_can_create_a_user(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('create')
            ->set('name', 'مستخدم جديد')
            ->set('email', 'new-user@example.com')
            ->set('password', 'a-strong-password')
            ->set('password_confirmation', 'a-strong-password')
            ->set('role', UserRole::Staff->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'مستخدم جديد',
            'email' => 'new-user@example.com',
            'role' => UserRole::Staff->value,
        ]);
    }

    public function test_creating_a_user_requires_a_unique_email(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();
        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('create')
            ->set('name', 'مستخدم آخر')
            ->set('email', 'taken@example.com')
            ->set('password', 'a-strong-password')
            ->set('password_confirmation', 'a-strong-password')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_super_admin_can_edit_a_users_name_and_email(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();
        $user = User::factory()->create(['name' => 'الاسم القديم']);

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('edit', $user->id)
            ->set('name', 'الاسم الجديد')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('الاسم الجديد', $user->fresh()->name);
    }

    public function test_super_admin_can_disable_and_reenable_a_user(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();
        $user = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('toggleDisabled', $user->id);

        $this->assertTrue($user->fresh()->isDisabled());

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('toggleDisabled', $user->id);

        $this->assertFalse($user->fresh()->isDisabled());
    }

    public function test_super_admin_cannot_disable_their_own_account(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();

        Livewire::actingAs($admin)
            ->test('pages::admin.users.index')
            ->call('toggleDisabled', $admin->id);

        $this->assertFalse($admin->fresh()->isDisabled());
    }

    public function test_disabled_user_cannot_log_in(): void
    {
        $user = User::factory()->role(UserRole::Librarian)->create([
            'email' => 'disabled@example.com',
            'password' => 'a-strong-password',
            'disabled_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => 'disabled@example.com',
            'password' => 'a-strong-password',
        ]);

        $this->assertGuest();
    }

    public function test_disabling_an_already_authenticated_user_logs_them_out_on_next_request(): void
    {
        $user = User::factory()->role(UserRole::Librarian)->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->update(['disabled_at' => now()]);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
