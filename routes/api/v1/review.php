<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Review
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\V1\Review\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('reviews')->group(function () {
    Route::post('/', [ReviewController::class, 'store'])->name('api.reviews.store');
    Route::put('/{review}', [ReviewController::class, 'update'])->name('api.reviews.update');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('api.reviews.destroy');
    Route::get('/my', [ReviewController::class, 'myReviews'])->name('api.reviews.my');
});
