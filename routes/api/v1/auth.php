<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Auth
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('api.auth.register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('api.auth.login');

        Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])
            ->middleware('throttle:3,1')
            ->name('api.auth.forgot');

        Route::post('/reset-password', [PasswordResetController::class, 'reset'])
            ->middleware('throttle:5,1')
            ->name('api.auth.reset');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.auth.profile.update');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('api.auth.password.update');

        Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:3,1')
            ->name('api.auth.email.resend');

        Route::get('/email/status', [EmailVerificationController::class, 'status'])->name('api.auth.email.status');
    });

});