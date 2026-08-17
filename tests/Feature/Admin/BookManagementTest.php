<?php

namespace Tests\Feature\Admin;

use App\Enums\BookStatus;
use App\Enums\UserRole;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guests_cannot_access_the_admin_books_page(): void
    {
        $this->get(route('admin.books.index'))->assertRedirect(route('login'));
    }

    public function test_non_staff_users_cannot_access_the_admin_books_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.books.index'))->assertForbidden();
    }

    public function test_librarian_can_view_the_admin_books_page(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        $this->actingAs($librarian)->get(route('admin.books.index'))->assertOk();
    }

    public function test_librarian_can_create_a_book(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $author = Author::factory()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', 'كتاب جديد للاختبار')
            ->set('author_id', $author->id)
            ->set('category_id', $category->id)
            ->set('status', BookStatus::Available->value)
            ->set('language', 'ar')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('books', [
            'title' => 'كتاب جديد للاختبار',
            'author_id' => $author->id,
        ]);
    }

    public function test_creating_a_book_requires_a_title(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', '')
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    }

    public function test_librarian_can_update_a_book(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $book = Book::factory()->create(['title' => 'العنوان القديم']);

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('edit', $book->id)
            ->set('title', 'العنوان الجديد')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('العنوان الجديد', $book->fresh()->title);
    }

    public function test_editor_role_cannot_manage_books(): void
    {
        $editor = User::factory()->role(UserRole::Editor)->create();
        $book = Book::factory()->create();

        $this->actingAs($editor)->get(route('admin.books.index'))->assertForbidden();

        Livewire::actingAs($editor)
            ->test('pages::admin.books.index')
            ->call('edit', $book->id)
            ->assertForbidden();
    }

    public function test_librarian_can_delete_a_book_with_no_dependents(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $book = Book::factory()->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('delete', $book->id);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_book_search_filters_the_admin_list(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        Book::factory()->create(['title' => 'كتاب الفقه المالكي']);
        Book::factory()->create(['title' => 'كتاب آخر تماماً']);

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->set('search', 'المالكي')
            ->assertSee('كتاب الفقه المالكي')
            ->assertDontSee('كتاب آخر تماماً');
    }

    public function test_isbn_must_be_unique(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        Book::factory()->create(['isbn' => '978-1234567890']);

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', 'كتاب برقم ردمك مكرر')
            ->set('isbn', '978-1234567890')
            ->call('save')
            ->assertHasErrors(['isbn' => 'unique']);
    }

    public function test_editing_a_book_can_keep_its_own_isbn(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $book = Book::factory()->create(['isbn' => '978-1234567890']);

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('edit', $book->id)
            ->set('title', 'عنوان محدّث')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_cover_upload_rejects_non_image_files(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', 'كتاب بغلاف غير صالح')
            ->set('cover', UploadedFile::fake()->create('not-an-image.pdf', 100))
            ->call('save')
            ->assertHasErrors(['cover' => 'image']);
    }

    public function test_cover_upload_rejects_oversized_images(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', 'كتاب بغلاف كبير جداً')
            ->set('cover', UploadedFile::fake()->image('cover.jpg')->size(5000))
            ->call('save')
            ->assertHasErrors(['cover' => 'max']);
    }

    public function test_cover_upload_stores_a_valid_image(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', 'كتاب بغلاف صالح')
            ->set('cover', UploadedFile::fake()->image('cover.jpg')->size(500))
            ->call('save')
            ->assertHasNoErrors();

        $book = Book::where('title', 'كتاب بغلاف صالح')->firstOrFail();
        $this->assertNotNull($book->cover_image);
    }

    public function test_staff_role_cannot_manage_books(): void
    {
        $staff = User::factory()->role(UserRole::Staff)->create();

        $this->actingAs($staff)->get(route('admin.books.index'))->assertForbidden();
    }

    public function test_librarian_can_create_a_book_with_multiple_editors_commentators_and_annotators(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', 'كتاب بمحققين متعددين')
            ->set('editors', ['أحمد شاكر', 'محمود شاكر'])
            ->set('commentators', ['ابن حجر'])
            ->set('annotators', ['السيوطي', ''])
            ->call('save')
            ->assertHasNoErrors();

        $book = Book::where('title', 'كتاب بمحققين متعددين')->firstOrFail();
        $this->assertSame(['أحمد شاكر', 'محمود شاكر'], $book->editors);
        $this->assertSame(['ابن حجر'], $book->commentators);
        // The trailing blank slot from the repeatable-input UI must be dropped, not stored.
        $this->assertSame(['السيوطي'], $book->annotators);
    }

    public function test_leaving_contributor_fields_empty_stores_null_not_an_empty_array(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->set('title', 'كتاب بدون محققين')
            ->set('editors', [''])
            ->call('save')
            ->assertHasNoErrors();

        $book = Book::where('title', 'كتاب بدون محققين')->firstOrFail();
        $this->assertNull($book->editors);
    }

    public function test_add_and_remove_contributor_updates_the_list(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('create')
            ->call('addContributor', 'editors')
            ->assertSet('editors', ['', ''])
            ->set('editors.0', 'محقق أول')
            ->set('editors.1', 'محقق ثانٍ')
            ->call('removeContributor', 'editors', 0)
            ->assertSet('editors', ['محقق ثانٍ']);
    }

    public function test_editing_a_book_populates_existing_contributor_lists(): void
    {
        $librarian = User::factory()->role(UserRole::Librarian)->create();
        $book = Book::factory()->create(['editors' => ['أحمد شاكر'], 'commentators' => null]);

        Livewire::actingAs($librarian)
            ->test('pages::admin.books.index')
            ->call('edit', $book->id)
            ->assertSet('editors', ['أحمد شاكر'])
            ->assertSet('commentators', ['']);
    }
}
