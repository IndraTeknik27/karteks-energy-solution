<?php

/*
|--------------------------------------------------------------------------
| API v1 Payment Routes
|--------------------------------------------------------------------------
|
| Customer-facing payment endpoints (auth:sanctum):
| - initiate payment for an order
| - get payment status
| - refresh status from Midtrans
| - payment history
|
| Midtrans webhook (PUBLIC, no auth - signature validation di controller):
| - POST /api/v1/payments/midtrans/notification
|
*/

use App\Http\Controllers\Api\V1\Payment\MidtransController;
use App\Http\Controllers\Api\V1\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments')->middleware('auth:sanctum')->group(function () {
    Route::post('/orders/{orderNumber}/initiate', [MidtransController::class, 'initiate'])
        ->name('api.payments.initiate');
    Route::get('/orders/{orderNumber}/status', [PaymentController::class, 'status'])
        ->name('api.payments.status');
    Route::post('/orders/{orderNumber}/refresh', [PaymentController::class, 'refresh'])
        ->name('api.payments.refresh');
    Route::get('/history', [PaymentController::class, 'history'])
        ->name('api.payments.history');
});

Route::post('/payments/midtrans/notification', [MidtransController::class, 'notification'])
    ->name('api.payments.midtrans.notification')
    ->withoutMiddleware([\App\Http\Middleware\ForceJsonResponse::class])
    ->middleware('throttle:100,1');