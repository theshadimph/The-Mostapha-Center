<?php

namespace Tests\Feature\Library;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_the_suggestions_component(): void
    {
        $this->get(route('home'))->assertSeeLivewire('search-suggestions');
    }

    public function test_search_page_renders_the_suggestions_component_embedded(): void
    {
        // The search box only renders alongside existing results (see
        // library/search.blade.php's empty-state branch) — seed a book so
        // the page has something to list.
        Book::factory()->create();

        $this->get(route('search'))->assertSeeLivewire('search-suggestions');
    }

    public function test_search_page_seeds_the_component_with_the_current_query(): void
    {
        Book::factory()->create(['title' => 'فتح الباري بشرح صحيح البخاري']);

        Livewire::test('search-suggestions', ['value' => 'فتح الباري', 'standalone' => false])
            ->assertSet('query', 'فتح الباري')
            ->assertSet('standalone', false)
            ->assertSee('فتح الباري بشرح صحيح البخاري');
    }

    public function test_embedded_mode_does_not_render_its_own_form(): void
    {
        Livewire::test('search-suggestions', ['standalone' => false])
            ->assertDontSeeHtml('<form');
    }

    public function test_short_queries_return_no_suggestions(): void
    {
        Book::factory()->create(['title' => 'فتح الباري بشرح صحيح البخاري']);

        Livewire::test('search-suggestions')
            ->set('query', 'ف')
            ->assertSet('suggestions', fn ($suggestions) => $suggestions->isEmpty());
    }

    public function test_matching_query_returns_the_book_with_its_author_loaded(): void
    {
        $author = Author::factory()->create(['name' => 'ابن حجر العسقلاني']);
        $book = Book::factory()->create([
            'title' => 'فتح الباري بشرح صحيح البخاري',
            'author_id' => $author->id,
        ]);
        Book::factory()->create(['title' => 'كتاب غير ذي صلة تماماً']);

        Livewire::test('search-suggestions')
            ->set('query', 'الباري')
            ->assertSee('فتح الباري بشرح صحيح البخاري')
            ->assertSee('ابن حجر العسقلاني')
            ->assertDontSee('كتاب غير ذي صلة تماماً');

        $suggestions = Livewire::test('search-suggestions')
            ->set('query', 'الباري')
            ->instance()->suggestions;

        $this->assertTrue($suggestions->first()->is($book));
        $this->assertTrue($suggestions->first()->relationLoaded('author'));
    }

    public function test_suggestion_links_to_the_book_show_page(): void
    {
        $book = Book::factory()->create(['title' => 'زاد المعاد في هدي خير العباد']);

        Livewire::test('search-suggestions')
            ->set('query', 'زاد المعاد')
            ->assertSee(route('books.show', $book));
    }

    public function test_no_results_message_shown_for_unmatched_query(): void
    {
        Book::factory()->create(['title' => 'كتاب موجود']);

        Livewire::test('search-suggestions')
            ->set('query', 'xyzxyz')
            ->assertSee('لا توجد نتائج مطابقة');
    }
}
