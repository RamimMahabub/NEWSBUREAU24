<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/bn'));
Route::get('/media/{media}', [NewsController::class, 'media'])->name('media.show');
Route::prefix('{locale}')->whereIn('locale', ['bn', 'en'])->group(function () {
    Route::get('/', [NewsController::class, 'home'])->name('home');
    Route::get('/search', [NewsController::class, 'search'])->name('search');
    Route::get('/story/{slug}', [NewsController::class, 'article'])->name('article');
    Route::get('/info/{page}', [NewsController::class, 'page'])->name('page');
    Route::post('/newsletter', [NewsController::class, 'subscribe'])->middleware('throttle:5,1')->name('subscribe');
});
