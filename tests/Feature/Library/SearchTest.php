<?php

namespace Tests\Feature\Library;

use App\Enums\BookStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_renders_without_a_query(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get(route('search'));

        $response->assertOk();
    }

    public function test_search_matches_by_title(): void
    {
        Book::factory()->create(['title' => 'فتح الباري بشرح صحيح البخاري']);
        Book::factory()->create(['title' => 'زاد المعاد']);

        $response = $this->get(route('search', ['q' => 'الباري']));

        $response->assertOk();
        $response->assertSee('فتح الباري بشرح صحيح البخاري');
        $response->assertDontSee('زاد المعاد');
    }

    public function test_search_matches_by_author_name(): void
    {
        $author = Author::factory()->create(['name' => 'ابن القيم الجوزية']);
        $otherAuthor = Author::factory()->create(['name' => 'مؤلف مختلف تماماً']);
        Book::factory()->create(['title' => 'كتاب لا علاقة له', 'author_id' => $author->id]);
        Book::factory()->create(['title' => 'كتاب آخر', 'author_id' => $otherAuthor->id]);

        $response = $this->get(route('search', ['q' => 'ابن القيم']));

        $response->assertSee('كتاب لا علاقة له');
        $response->assertDontSee('كتاب آخر');
    }

    public function test_search_matches_by_editor_commentator_or_annotator_name(): void
    {
        Book::factory()->create(['title' => 'كتاب محقق', 'editors' => ['أحمد شاكر']]);
        Book::factory()->create(['title' => 'كتاب مشروح', 'commentators' => ['ابن حجر العسقلاني']]);
        Book::factory()->create(['title' => 'كتاب مُحشّى', 'annotators' => ['السيوطي']]);
        Book::factory()->create(['title' => 'كتاب لا علاقة له']);

        $this->get(route('search', ['q' => 'شاكر']))
            ->assertSee('كتاب محقق')->assertDontSee('كتاب لا علاقة له');

        $this->get(route('search', ['q' => 'ابن حجر العسقلاني']))
            ->assertSee('كتاب مشروح')->assertDontSee('كتاب لا علاقة له');

        $this->get(route('search', ['q' => 'السيوطي']))
            ->assertSee('كتاب مُحشّى')->assertDontSee('كتاب لا علاقة له');
    }

    public function test_search_filters_by_category(): void
    {
        $fiqh = Category::factory()->create(['name' => 'الفقه']);
        $tarikh = Category::factory()->create(['name' => 'التاريخ']);

        Book::factory()->create(['title' => 'كتاب الفقه', 'category_id' => $fiqh->id]);
        Book::factory()->create(['title' => 'كتاب التاريخ', 'category_id' => $tarikh->id]);

        $response = $this->get(route('search', ['category' => $fiqh->slug]));

        $response->assertSee('كتاب الفقه');
        $response->assertDontSee('كتاب التاريخ');
    }

    public function test_search_shows_empty_state_for_no_matches(): void
    {
        Book::factory()->create(['title' => 'كتاب موجود']);

        $response = $this->get(route('search', ['q' => 'nonexistent-xyz']));

        $response->assertOk();
        $response->assertSee('لم نجد ما تبحث عنه بالضبط');
    }

    public function test_search_results_are_paginated(): void
    {
        Book::factory()->count(20)->create(['status' => BookStatus::Available]);

        $response = $this->get(route('search'));

        $response->assertOk();
        $response->assertViewHas('results', fn ($results) => $results->total() === 20 && $results->count() === 12);
    }
}
