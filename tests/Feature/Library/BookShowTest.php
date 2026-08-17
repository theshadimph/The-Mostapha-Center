<?php

namespace Tests\Feature\Library;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\LibrarySection;
use App\Models\Shelf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_show_page_renders(): void
    {
        $book = Book::factory()->create(['title' => 'فتح الباري']);

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
        $response->assertSee('فتح الباري');
    }

    public function test_unknown_book_slug_returns_404(): void
    {
        $response = $this->get('/books/does-not-exist');

        $response->assertNotFound();
    }

    public function test_viewing_a_book_increments_its_view_count(): void
    {
        $book = Book::factory()->create(['views_count' => 5]);

        $this->get(route('books.show', $book));

        $this->assertSame(6, $book->fresh()->views_count);
    }

    public function test_viewing_a_book_adds_it_to_recently_viewed_session(): void
    {
        $book = Book::factory()->create();

        $this->get(route('books.show', $book));

        $this->assertSame([$book->id], session('recently_viewed_books'));
    }

    public function test_book_location_page_shows_shelf_and_section(): void
    {
        $section = LibrarySection::factory()->create(['name' => 'قاعة العلوم الشرعية', 'code' => 'F01']);
        $shelf = Shelf::factory()->for($section, 'librarySection')->create(['code' => '05']);
        $book = Book::factory()->onShelf($shelf, '12')->create();

        $response = $this->get(route('books.location', $book));

        $response->assertOk();
        $response->assertSee('قاعة العلوم الشرعية');
        $response->assertSee('F01-05-12');
    }

    public function test_book_location_page_handles_unshelved_book(): void
    {
        $book = Book::factory()->create(['shelf_id' => null, 'position' => null]);

        $response = $this->get(route('books.location', $book));

        $response->assertOk();
        $response->assertSee('لم يُحدَّد موقع هذا الكتاب بعد');
    }

    public function test_book_qr_endpoint_returns_an_svg(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.qr', $book));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_book_show_uses_the_authors_and_categories_relationships(): void
    {
        $author = Author::factory()->create(['name' => 'ابن حجر العسقلاني']);
        $category = Category::factory()->create(['name' => 'الحديث الشريف']);
        $book = Book::factory()->create(['author_id' => $author->id, 'category_id' => $category->id]);

        $response = $this->get(route('books.show', $book));

        $response->assertSee('ابن حجر العسقلاني');
        $response->assertSee('الحديث الشريف');
    }

    public function test_book_show_page_displays_editors_commentators_and_annotators(): void
    {
        $book = Book::factory()->create([
            'editors' => ['أحمد شاكر', 'محمود شاكر'],
            'commentators' => ['ابن حجر'],
            'annotators' => ['السيوطي'],
        ]);

        $response = $this->get(route('books.show', $book));

        $response->assertSee('أحمد شاكر');
        $response->assertSee('محمود شاكر');
        $response->assertSee('ابن حجر');
        $response->assertSee('السيوطي');
    }

    public function test_book_show_page_omits_contributor_sections_when_empty(): void
    {
        $book = Book::factory()->create(['editors' => null, 'commentators' => null, 'annotators' => null]);

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
        $response->assertDontSee('المحققون');
        $response->assertDontSee('الشارحون');
        $response->assertDontSee('المحشّون');
    }
}
