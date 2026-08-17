<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/activities', [PageController::class, 'activities'])->name('activities');
Route::get('/donations', [PageController::class, 'donations'])->name('donations');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactStore'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

Route::middleware(['auth', 'verified', 'can:access-admin'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    require __DIR__.'/admin.php';
});

require __DIR__.'/settings.php';
require __DIR__.'/library.php';
