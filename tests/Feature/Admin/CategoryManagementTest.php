<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_staff_cannot_access_category_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
    }

    public function test_librarian_can_create_a_category_with_a_generated_slug(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.categories.index')
            ->call('create')
            ->set('name', 'قسم اختبار')
            ->call('save')
            ->assertHasNoErrors();

        $category = Category::where('name', 'قسم اختبار')->firstOrFail();
        $this->assertNotEmpty($category->slug);
    }

    public function test_cannot_delete_a_category_that_has_books(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $category = Category::factory()->create();
        Book::factory()->create(['category_id' => $category->id]);

        Livewire::actingAs($librarian)
            ->test('pages::admin.categories.index')
            ->call('delete', $category->id);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_can_delete_an_empty_category(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $category = Category::factory()->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.categories.index')
            ->call('delete', $category->id);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_can_create_a_subcategory_under_a_parent(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $parent = Category::factory()->create(['name' => 'الفقه الإسلامي']);

        Livewire::actingAs($librarian)
            ->test('pages::admin.categories.index')
            ->call('create')
            ->set('name', 'الفقه المقارن')
            ->set('parent_id', $parent->id)
            ->call('save')
            ->assertHasNoErrors();

        $child = Category::where('name', 'الفقه المقارن')->firstOrFail();
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertTrue($parent->fresh()->children->contains('id', $child->id));
    }

    public function test_a_category_cannot_be_set_as_its_own_parent(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $category = Category::factory()->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.categories.index')
            ->call('edit', $category->id)
            ->set('parent_id', $category->id)
            ->call('save')
            ->assertHasErrors(['parent_id']);
    }

    public function test_cannot_delete_a_category_that_has_subcategories(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        Livewire::actingAs($librarian)
            ->test('pages::admin.categories.index')
            ->call('delete', $parent->id);

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }
}
