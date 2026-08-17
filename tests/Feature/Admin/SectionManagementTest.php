<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\LibrarySection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SectionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_librarian_can_create_a_section(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.sections.index')
            ->call('createSection')
            ->set('name', 'قاعة جديدة')
            ->set('code', 'F09')
            ->call('saveSection')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('library_sections', ['code' => 'F09']);
    }

    public function test_section_code_must_be_unique(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        LibrarySection::factory()->create(['code' => 'F01']);

        Livewire::actingAs($librarian)
            ->test('pages::admin.sections.index')
            ->call('createSection')
            ->set('name', 'قاعة مكررة')
            ->set('code', 'F01')
            ->call('saveSection')
            ->assertHasErrors(['code']);
    }

    public function test_can_add_a_shelf_to_a_section(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $section = LibrarySection::factory()->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.sections.index')
            ->call('manageShelves', $section->id)
            ->set('newShelfCode', '99')
            ->call('addShelf')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('shelves', ['library_section_id' => $section->id, 'code' => '99']);
    }
}
