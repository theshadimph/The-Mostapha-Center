<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_librarian_can_create_an_author(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.authors.index')
            ->call('create')
            ->set('name', 'مؤلف اختبار')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('authors', ['name' => 'مؤلف اختبار']);
    }

    public function test_super_admin_can_manage_authors(): void
    {
        $admin = User::factory()->role(UserRole::SuperAdmin)->create();

        $this->actingAs($admin)->get(route('admin.authors.index'))->assertOk();
    }

    public function test_staff_role_without_catalog_permission_cannot_manage_authors(): void
    {
        $staff = User::factory()->role(UserRole::Staff)->create();

        $this->actingAs($staff)->get(route('admin.authors.index'))->assertForbidden();
    }

    public function test_updating_an_author_persists_changes(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $author = Author::factory()->create(['name' => 'اسم قديم']);

        Livewire::actingAs($librarian)
            ->test('pages::admin.authors.index')
            ->call('edit', $author->id)
            ->set('name', 'اسم جديد')
            ->call('save');

        $this->assertSame('اسم جديد', $author->fresh()->name);
    }
}
