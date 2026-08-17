<?php

namespace Tests\Feature\Library;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_home_page_shows_popular_available_books(): void
    {
        $popular = Book::factory()->create([
            'title' => 'الكتاب الأكثر طلباً',
            'status' => BookStatus::Available,
            'views_count' => 999,
        ]);
        Book::factory()->create([
            'title' => 'كتاب غير متوفر',
            'status' => BookStatus::Unavailable,
            'views_count' => 5000,
        ]);

        $response = $this->get(route('home'));

        $response->assertSee('الكتاب الأكثر طلباً');
        $response->assertDontSee('كتاب غير متوفر');
    }

    public function test_home_page_shows_real_categories(): void
    {
        Category::factory()->create(['name' => 'قسم اختبار فريد']);

        $response = $this->get(route('home'));

        $response->assertSee('قسم اختبار فريد');
    }

    public function test_home_page_shows_recently_viewed_books_from_session(): void
    {
        $book = Book::factory()->create(['title' => 'كتاب تمت زيارته']);

        $this->get(route('books.show', $book));
        $response = $this->get(route('home'));

        $response->assertSee('كتاب تمت زيارته');
    }
}
