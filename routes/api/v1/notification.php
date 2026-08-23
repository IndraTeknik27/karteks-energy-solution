<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Notifications
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read-all');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('api.notifications.destroy');

    // FCM token management untuk Flutter
    Route::post('/fcm-token', [NotificationController::class, 'registerFcmToken'])->name('api.notifications.fcm.register');
    Route::delete('/fcm-token', [NotificationController::class, 'unregisterFcmToken'])->name('api.notifications.fcm.unregister');
});
