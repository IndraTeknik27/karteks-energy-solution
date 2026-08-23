<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Order
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Order\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/{orderNumber}', [OrderController::class, 'show'])->name('api.orders.show');
    Route::post('/{orderNumber}/cancel', [OrderController::class, 'cancel'])->name('api.orders.cancel');
    Route::post('/{orderNumber}/confirm-delivery', [OrderController::class, 'confirmDelivery'])->name('api.orders.confirm-delivery');
    Route::get('/{orderNumber}/tracking', [OrderController::class, 'tracking'])->name('api.orders.tracking');
    Route::get('/{orderNumber}/invoice', [OrderController::class, 'invoice'])->name('api.orders.invoice');
});
