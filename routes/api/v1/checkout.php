<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Checkout
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Checkout\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('checkout')->group(function () {
    Route::post('/preview', [CheckoutController::class, 'preview'])
        ->middleware('throttle:checkout')
        ->name('api.checkout.preview');
    Route::post('/place-order', [CheckoutController::class, 'placeOrder'])
        ->middleware('throttle:order-create')
        ->name('api.checkout.place');
    Route::post('/validate-stock', [CheckoutController::class, 'validateStock'])
        ->middleware('throttle:checkout')
        ->name('api.checkout.validate-stock');
});
