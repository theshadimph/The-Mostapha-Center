<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $categorySlug = $request->query('category');
        $category = is_string($categorySlug) ? Category::where('slug', $categorySlug)->first() : null;

        $results = $query !== ''
            ? $this->searchViaScout($query, $category)
            : $this->browseCategory($category);

        return view('library.search', [
            'query' => $query,
            'results' => $results,
            'categories' => Category::query()->orderBy('name')->get(),
            'activeCategory' => $categorySlug,
            'suggestions' => $query !== '' && $results->isEmpty()
                ? Book::query()->with('author')->inRandomOrder()->take(4)->get()
                : collect(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Book>
     */
    protected function searchViaScout(string $query, ?Category $category): LengthAwarePaginator
    {
        return Book::search($query)
            ->when($category, fn ($search) => $search->where('category_id', $category->id))
            ->query(fn ($eloquent) => $eloquent->with(['author', 'category']))
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * No search term — plain browsing, so this stays a normal Eloquent
     * query rather than round-tripping to the search engine.
     *
     * @return LengthAwarePaginator<int, Book>
     */
    protected function browseCategory(?Category $category): LengthAwarePaginator
    {
        return Book::query()
            ->inCategory($category)
            ->with(['author', 'category'])
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();
    }
}
