<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Checkout
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Checkout\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('checkout')->group(function () {
    Route::post('/preview', [CheckoutController::class, 'preview'])->name('api.checkout.preview');
    Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('api.checkout.place');
    Route::post('/validate-stock', [CheckoutController::class, 'validateStock'])->name('api.checkout.validate-stock');
});
