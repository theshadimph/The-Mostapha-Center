<?php

namespace App\Http\Controllers;

use App\Enums\BookStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookMovement;
use App\Models\Category;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'totalBooks' => Book::query()->count(),
            'availableBooks' => Book::query()->where('status', BookStatus::Available)->count(),
            'borrowedBooks' => Book::query()->where('status', BookStatus::Borrowed)->count(),
            'unshelvedBooks' => Book::query()->whereNull('shelf_id')->count(),
            'totalAuthors' => Author::query()->count(),
            'totalCategories' => Category::query()->count(),
            'recentBooks' => Book::query()->with(['author', 'category'])->latest()->take(6)->get(),
            'recentMovements' => BookMovement::query()->with(['book', 'toShelf.librarySection', 'movedBy'])->latest()->take(6)->get(),
            'categoryBreakdown' => Category::query()->withCount('books')->orderByDesc('books_count')->take(6)->get(),
        ]);
    }
}
