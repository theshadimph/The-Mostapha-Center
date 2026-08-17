<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Support\RecentlyViewedBooks;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BookController extends Controller
{
    public function show(Book $book): View
    {
        $book->load(['author', 'category', 'shelf.librarySection']);

        $book->increment('views_count');
        RecentlyViewedBooks::remember($book->id);

        $relatedBooks = Book::query()
            ->when($book->category_id, fn ($query) => $query->where('category_id', $book->category_id))
            ->where('id', '!=', $book->id)
            ->with('author')
            ->take(4)
            ->get();

        return view('library.books.show', [
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ]);
    }

    public function location(Book $book): View
    {
        $book->load(['shelf.librarySection']);

        return view('library.books.location', [
            'book' => $book,
        ]);
    }

    /**
     * Printable QR label for this book — encodes the public location page
     * URL so a phone camera lands a visitor straight on "where is this book".
     */
    public function qr(Book $book): Response
    {
        $result = (new Builder(writer: new SvgWriter))->build(
            data: route('books.location', $book),
            size: 320,
            margin: 12,
        );

        return response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType())
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
