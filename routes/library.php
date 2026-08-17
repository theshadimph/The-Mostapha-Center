<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('search', [SearchController::class, 'index'])->name('search');

Route::get('books/{book:slug}', [BookController::class, 'show'])->name('books.show');

Route::get('books/{book:slug}/location', [BookController::class, 'location'])->name('books.location');

Route::get('books/{book:slug}/qr', [BookController::class, 'qr'])->name('books.qr');
