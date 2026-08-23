<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| KARTEKS ENERGY SOLUTION - API Routes
|--------------------------------------------------------------------------
|
| Base prefix: /api
| Versioning: /api/v1/...
|
| Semua business logic dipisahkan ke Services & Actions.
| API ini akan digunakan oleh:
| - Website frontend (Blade)
| - Mobile app (Flutter - Android & iOS)
| - Third-party integration (jika ada)
|
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'KARTEKS ENERGY SOLUTION API is running',
        'data' => [
            'service' => 'karteks-energy-solution-api',
            'version' => '1.0.0',
            'environment' => app()->environment(),
            'timestamp' => now()->toIso8601String(),
        ],
    ]);
});

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Public routes - tidak butuh authentication
    require __DIR__.'/api/v1/public.php';

    // Public auth routes (register, login, forgot/reset password) - tidak butuh auth:sanctum
    require __DIR__.'/api/v1/auth.php';

    // Cart routes - accessible untuk guest + authenticated (mixed mode)
    require __DIR__.'/api/v1/cart.php';

    // Payment routes (mixed mode: customer-facing protected + webhook public)
    require __DIR__.'/api/v1/payment.php';

    // Authenticated routes - butuh Sanctum token
    Route::middleware(['auth:sanctum'])->group(function () {
        require __DIR__.'/api/v1/customer.php';
        require __DIR__.'/api/v1/checkout.php';
        require __DIR__.'/api/v1/order.php';
        require __DIR__.'/api/v1/wishlist.php';
        require __DIR__.'/api/v1/review.php';
        require __DIR__.'/api/v1/quotation.php';
        require __DIR__.'/api/v1/booking.php';
        require __DIR__.'/api/v1/custom-battery.php';
        require __DIR__.'/api/v1/notification.php';
    });

});
