<?php

use App\Http\Controllers\Web\ProfileAddressController;
use App\Http\Controllers\Web\DashboardOrderController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\ServicePageController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\WishlistController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\CustomBatteryController;
use App\Http\Controllers\Web\QuotationController;
use App\Http\Controllers\Web\ServiceBookingController;
use App\Http\Controllers\Web\BannerClickController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\RobotsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// SEO: Sitemap + Robots (public, no auth)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.xml');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots.txt');

// Banner click tracking (public, fire-and-forget endpoint)
Route::post('/banners/{banner}/click', [BannerClickController::class, 'click'])->name('public.banner.click');

// Catalog
Route::get('/products', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Services
Route::get('/services', [ServicePageController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServicePageController::class, 'show'])->name('services.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Pages
Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');

// Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.forgot.attempt');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.attempt');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer dashboard
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/orders', [DashboardOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{orderNumber}', [DashboardOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{orderNumber}/cancel', [DashboardOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{orderNumber}/invoice', [DashboardOrderController::class, 'invoice'])->name('orders.invoice');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/addresses', [ProfileAddressController::class, 'index'])->name('addresses');
    Route::get('/profile/addresses/create', [ProfileAddressController::class, 'create'])->name('addresses.create');
    Route::post('/profile/addresses', [ProfileAddressController::class, 'store'])->name('addresses.store');
    Route::get('/profile/addresses/{address}/edit', [ProfileAddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/profile/addresses/{address}', [ProfileAddressController::class, 'update'])->name('addresses.update');
    Route::delete('/profile/addresses/{address}', [ProfileAddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/profile/addresses/{address}/primary', [ProfileAddressController::class, 'setPrimary'])->name('addresses.primary');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/remove/{product:slug}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::post('/wishlist/move-to-cart/{product:slug}', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'myReviews'])->name('review.my');
    Route::get('/products/{product:slug}/review', [ReviewController::class, 'create'])->name('review.create');
    Route::post('/products/{product:slug}/review', [ReviewController::class, 'store'])->name('review.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('review.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('review.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('review.destroy');
});

// Checkout
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/preview', [CheckoutController::class, 'preview'])->name('preview');
    Route::post('/place', [CheckoutController::class, 'place'])->name('place');
});

// Payment (Midtrans redirect pages)
Route::middleware('auth')->prefix('payment')->name('payment.')->group(function () {
    Route::get('/orders/{orderNumber}', [PaymentController::class, 'show'])->name('show');
    Route::get('/orders/{orderNumber}/finish', [PaymentController::class, 'finish'])->name('finish');
    Route::get('/orders/{orderNumber}/unfinish', [PaymentController::class, 'unfinish'])->name('unfinish');
    Route::get('/orders/{orderNumber}/error', [PaymentController::class, 'error'])->name('error');
});

// Custom Battery Request (customer-facing)
Route::middleware('auth')->prefix('dashboard/custom-battery')->name('dashboard.custom-battery.')->group(function () {
    Route::get('/', [CustomBatteryController::class, 'index'])->name('index');
    Route::get('/create', [CustomBatteryController::class, 'create'])->name('create');
    Route::post('/', [CustomBatteryController::class, 'store'])->name('store');
    Route::get('/{request}', [CustomBatteryController::class, 'show'])->name('show');
    Route::post('/{request}/cancel', [CustomBatteryController::class, 'cancel'])->name('cancel');
    Route::post('/{request}/files', [CustomBatteryController::class, 'uploadFile'])->name('file.upload');
    Route::post('/{request}/revisions/{revision}/respond', [CustomBatteryController::class, 'respondRevision'])->name('revision.respond')->whereNumber('revision');
});

// Quotation (customer-facing)
Route::middleware('auth')->prefix('dashboard/quotation')->name('dashboard.quotation.')->group(function () {
    Route::get('/', [QuotationController::class, 'index'])->name('index');
    Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
    Route::post('/{quotation}/accept', [QuotationController::class, 'accept'])->name('accept');
    Route::post('/{quotation}/reject', [QuotationController::class, 'reject'])->name('reject');
});

// Service Booking (customer-facing)
Route::middleware('auth')->prefix('dashboard/booking')->name('dashboard.booking.')->group(function () {
    Route::get('/', [ServiceBookingController::class, 'index'])->name('index');
    Route::get('/create', [ServiceBookingController::class, 'create'])->name('create');
    Route::get('/create/{serviceSlug}', [ServiceBookingController::class, 'create'])->name('create.service');
    Route::post('/', [ServiceBookingController::class, 'store'])->name('store');
    Route::get('/slots', [ServiceBookingController::class, 'getSlots'])->name('slots');
    Route::get('/{booking}', [ServiceBookingController::class, 'show'])->name('show');
    Route::post('/{booking}/reschedule', [ServiceBookingController::class, 'reschedule'])->name('reschedule');
    Route::post('/{booking}/cancel', [ServiceBookingController::class, 'cancel'])->name('cancel');
});