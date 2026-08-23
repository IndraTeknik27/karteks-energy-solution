<?php

/*
|--------------------------------------------------------------------------
| API Routes - Cart (accessible untuk guest + auth)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Cart\CartController;
use App\Http\Middleware\OptionalSanctumAuth;
use Illuminate\Support\Facades\Route;

/*
| Cart mendukung guest (session-based) dan authenticated (token-based).
| Guest: gunakan header X-Session-Id (uuid). Auto-generate jika tidak ada.
| Auth: token Sanctum otomatis diasosiasikan dengan customer.
| Middleware OptionalSanctumAuth: authenticate jika token ada, tidak error jika tidak.
*/
Route::middleware(OptionalSanctumAuth::class)->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('api.cart.index');
    Route::post('/items', [CartController::class, 'addItem'])->name('api.cart.add');
    Route::put('/items/{item}', [CartController::class, 'updateItem'])->name('api.cart.update');
    Route::delete('/items/{item}', [CartController::class, 'removeItem'])->name('api.cart.remove');
    Route::delete('/', [CartController::class, 'clear'])->name('api.cart.clear');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('api.cart.coupon.apply');
    Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('api.cart.coupon.remove');
    Route::post('/shipping', [CartController::class, 'calculateShipping'])->name('api.cart.shipping');
});