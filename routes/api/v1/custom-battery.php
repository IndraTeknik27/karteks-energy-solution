<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Custom Battery Request
|--------------------------------------------------------------------------
|
| Customer-facing endpoints (auth:sanctum):
| - GET  /options                  → chemistry, voltage, applications
| - GET  /                         → list customer's requests
| - POST /                         → submit new request
| - GET  /{request}                → detail
| - PUT  /{request}                → update (only on submitted/revision_requested)
| - POST /{request}/cancel         → cancel request
| - GET  /{request}/files          → list files
| - POST /{request}/files          → upload file
| - DELETE /{request}/files/{file} → delete file
| - GET  /{request}/revisions      → list revisions
| - POST /{request}/revisions      → admin: request revision (only admin route in practice)
| - POST /{request}/revisions/{revision}/respond → customer: respond to revision
| - POST /{request}/revisions/{revision}/accept  → customer: accept revision
| - POST /{request}/transition     → admin: change status
*/

use App\Http\Controllers\Api\V1\CustomBattery\CustomBatteryRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('custom-battery')->group(function () {
    Route::get('/options', [CustomBatteryRequestController::class, 'options'])
        ->name('api.custom-battery.options');

    Route::get('/', [CustomBatteryRequestController::class, 'index'])
        ->name('api.custom-battery.index');
    Route::post('/', [CustomBatteryRequestController::class, 'store'])
        ->name('api.custom-battery.store');

    Route::get('/{request}', [CustomBatteryRequestController::class, 'show'])
        ->name('api.custom-battery.show');
    Route::put('/{request}', [CustomBatteryRequestController::class, 'update'])
        ->name('api.custom-battery.update');
    Route::post('/{request}/cancel', [CustomBatteryRequestController::class, 'cancel'])
        ->name('api.custom-battery.cancel');

    Route::get('/{request}/files', [CustomBatteryRequestController::class, 'listFiles'])
        ->name('api.custom-battery.files.index');
    Route::post('/{request}/files', [CustomBatteryRequestController::class, 'uploadFile'])
        ->name('api.custom-battery.files.upload');
    Route::delete('/{request}/files/{file}', [CustomBatteryRequestController::class, 'deleteFile'])
        ->name('api.custom-battery.files.delete')
        ->whereNumber('file');

    Route::get('/{request}/revisions', [CustomBatteryRequestController::class, 'listRevisions'])
        ->name('api.custom-battery.revisions.index');
    Route::post('/{request}/revisions', [CustomBatteryRequestController::class, 'requestRevision'])
        ->name('api.custom-battery.revisions.create');
    Route::post('/{request}/revisions/{revision}/respond', [CustomBatteryRequestController::class, 'respondRevision'])
        ->name('api.custom-battery.revisions.respond')
        ->whereNumber('revision');
    Route::post('/{request}/revisions/{revision}/accept', [CustomBatteryRequestController::class, 'acceptRevision'])
        ->name('api.custom-battery.revisions.accept')
        ->whereNumber('revision');

    Route::post('/{request}/transition', [CustomBatteryRequestController::class, 'transitionStatus'])
        ->name('api.custom-battery.transition');
});
