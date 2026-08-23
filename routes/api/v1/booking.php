<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Service Booking
|--------------------------------------------------------------------------
|
| Customer-facing endpoints (auth:sanctum):
| - GET  /available-slots               → list available slots for service+date
| - GET  /                              → list customer's bookings
| - POST /                              → create new booking
| - GET  /{booking}                     → detail + status history
| - PUT  /{booking}/reschedule          → customer: request new schedule
| - POST /{booking}/cancel              → customer: cancel
|
| Admin/staff endpoints (auth:sanctum + admin role):
| - GET    /admin                       → list all bookings
| - GET    /admin/{booking}             → detail
| - POST   /admin/{booking}/confirm     → confirm + assign technician (optional)
| - POST   /admin/{booking}/assign      → assign technician only
| - POST   /admin/{booking}/reschedule  → admin reschedule
| - POST   /admin/{booking}/start       → mark in_progress
| - POST   /admin/{booking}/complete    → mark completed (with final_cost)
| - POST   /admin/{booking}/cancel      → admin cancel
*/

use App\Http\Controllers\Api\V1\Booking\ServiceBookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('bookings')->group(function () {
    Route::get('/available-slots', [ServiceBookingController::class, 'availableSlots'])
        ->name('api.bookings.slots');
    Route::get('/', [ServiceBookingController::class, 'index'])
        ->name('api.bookings.index');
    Route::post('/', [ServiceBookingController::class, 'store'])
        ->name('api.bookings.store');
    Route::get('/{booking}', [ServiceBookingController::class, 'show'])
        ->name('api.bookings.show');
    Route::put('/{booking}/reschedule', [ServiceBookingController::class, 'reschedule'])
        ->name('api.bookings.reschedule');
    Route::post('/{booking}/cancel', [ServiceBookingController::class, 'cancel'])
        ->name('api.bookings.cancel');

    Route::prefix('admin')->group(function () {
        Route::get('/', [ServiceBookingController::class, 'adminIndex'])
            ->name('api.bookings.admin.index');
        Route::get('/{booking}', [ServiceBookingController::class, 'adminShow'])
            ->name('api.bookings.admin.show');
        Route::post('/{booking}/confirm', [ServiceBookingController::class, 'confirm'])
            ->name('api.bookings.admin.confirm');
        Route::post('/{booking}/assign', [ServiceBookingController::class, 'assignTechnician'])
            ->name('api.bookings.admin.assign');
        Route::post('/{booking}/reschedule', [ServiceBookingController::class, 'adminReschedule'])
            ->name('api.bookings.admin.reschedule');
        Route::post('/{booking}/start', [ServiceBookingController::class, 'start'])
            ->name('api.bookings.admin.start');
        Route::post('/{booking}/complete', [ServiceBookingController::class, 'complete'])
            ->name('api.bookings.admin.complete');
        Route::post('/{booking}/cancel', [ServiceBookingController::class, 'adminCancel'])
            ->name('api.bookings.admin.cancel');
    });
});