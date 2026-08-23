<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Wishlist
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Customer\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('wishlist')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('api.wishlist.index');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('api.wishlist.toggle');
    Route::delete('/clear', [WishlistController::class, 'clear'])->name('api.wishlist.clear');
    Route::post('/move-to-cart/{product}', [WishlistController::class, 'moveToCart'])->name('api.wishlist.move-to-cart');
});
