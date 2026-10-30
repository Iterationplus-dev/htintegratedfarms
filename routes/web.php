<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteDraftController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/livestock', 'livestock')->name('livestock');
    Route::get('/poultry', 'poultry')->name('poultry');
    Route::get('/palm-oil', 'palm')->name('palm');
    Route::get('/sustainability', 'sustainability')->name('sustainability');
    Route::get('/gallery', 'gallery')->name('gallery');
    Route::get('/partners', 'partners')->name('partners');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/quote', 'quote')->name('quote');
    Route::get('/privacy-policy', 'privacy')->name('privacy');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/cookie-policy', 'cookies')->name('cookies');
    Route::get('/sitemap', 'sitemap')->name('sitemap');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/news', [PostController::class, 'index'])->name('news.index');
Route::get('/news/{post}', [PostController::class, 'show'])->name('news.show');

Route::get('/quote/resume/{draft}', QuoteDraftController::class)
    ->middleware(['signed', 'throttle:20,1'])
    ->name('quote.resume');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap.xml');

Route::get('/health', HealthController::class)->middleware('throttle:60,1')->name('health');
