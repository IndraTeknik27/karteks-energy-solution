<?php

/*
|--------------------------------------------------------------------------
| Public API Routes (tanpa authentication)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Public\CategoryController;
use App\Http\Controllers\Api\V1\Public\BrandController;
use App\Http\Controllers\Api\V1\Public\ProductController;
use App\Http\Controllers\Api\V1\Public\ProductReviewController;
use App\Http\Controllers\Api\V1\Public\ServiceController;
use App\Http\Controllers\Api\V1\Public\BlogController;
use App\Http\Controllers\Api\V1\Public\PageController;
use App\Http\Controllers\Api\V1\Public\BannerController;
use App\Http\Controllers\Api\V1\Public\FaqController;
use App\Http\Controllers\Api\V1\Public\TestimonialController;
use App\Http\Controllers\Api\V1\Public\SiteSettingController;
use App\Http\Controllers\Api\V1\Public\ShippingController;
use Illuminate\Support\Facades\Route;

// Email verification - signed URL, accessed from email link in browser
Route::get('/auth/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('api.auth.email.verify');

// Catalog - public read
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/featured', [ProductController::class, 'featured'])->name('api.products.featured');
    Route::get('/best-sellers', [ProductController::class, 'bestSellers'])->name('api.products.best-sellers');
    Route::get('/new-arrivals', [ProductController::class, 'newArrivals'])->name('api.products.new-arrivals');
    Route::get('/{slug}', [ProductController::class, 'show'])->name('api.products.show');
    Route::get('/{slug}/related', [ProductController::class, 'related'])->name('api.products.related');
    Route::get('/{slug}/reviews', [ProductReviewController::class, 'index'])->name('api.products.reviews.index');
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/tree', [CategoryController::class, 'tree'])->name('api.categories.tree');
    Route::get('/{slug}', [CategoryController::class, 'show'])->name('api.categories.show');
});

Route::prefix('brands')->group(function () {
    Route::get('/', [BrandController::class, 'index'])->name('api.brands.index');
    Route::get('/{slug}', [BrandController::class, 'show'])->name('api.brands.show');
});

Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('api.services.index');
    Route::get('/categories', [ServiceController::class, 'categories'])->name('api.services.categories');
    Route::get('/{slug}', [ServiceController::class, 'show'])->name('api.services.show');
});

// Content - public
Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('api.blog.index');
    Route::get('/categories', [BlogController::class, 'categories'])->name('api.blog.categories');
    Route::get('/tags', [BlogController::class, 'tags'])->name('api.blog.tags');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('api.blog.show');
});

Route::prefix('pages')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('api.pages.index');
    Route::get('/{slug}', [PageController::class, 'show'])->name('api.pages.show');
});

Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index'])->name('api.banners.index');
});

Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index'])->name('api.faqs.index');
});

Route::prefix('testimonials')->group(function () {
    Route::get('/', [TestimonialController::class, 'index'])->name('api.testimonials.index');
});

Route::prefix('settings')->group(function () {
    Route::get('/', [SiteSettingController::class, 'index'])->name('api.settings.index');
    Route::get('/shipping', [ShippingController::class, 'index'])->name('api.settings.shipping');
});

Route::post('/shipping/calculate', [ShippingController::class, 'calculate'])->name('api.shipping.calculate');
