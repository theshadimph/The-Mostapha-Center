<?php

use App\Http\Controllers\Admin\ImportRowErrorsController;
use App\Http\Controllers\Admin\ImportUploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('can:manage-catalog')->group(function () {
        Route::livewire('books', 'pages::admin.books.index')->name('books.index');
        Route::livewire('authors', 'pages::admin.authors.index')->name('authors.index');
        Route::livewire('categories', 'pages::admin.categories.index')->name('categories.index');
        Route::livewire('sections', 'pages::admin.sections.index')->name('sections.index');

        Route::livewire('import', 'pages::admin.import.index')->name('import.index');
        Route::post('import', [ImportUploadController::class, 'store'])->name('import.store');
        Route::livewire('import/{importBatch}', 'pages::admin.import.show')->name('import.show');
        Route::get('import/{importBatch}/errors', ImportRowErrorsController::class)->name('import.errors');
    });

    Route::middleware('can:manage-users')->group(function () {
        Route::livewire('users', 'pages::admin.users.index')->name('users.index');
    });
});
