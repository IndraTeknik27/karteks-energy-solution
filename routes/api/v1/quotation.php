<?php

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Quotation
|--------------------------------------------------------------------------
|
| Customer-facing endpoints (auth:sanctum):
| - GET    /                              → list customer's quotations
| - GET    /{quotation}                   → detail (auto-mark viewed)
| - POST   /{quotation}/accept            → accept quotation
| - POST   /{quotation}/reject            → reject with reason
| - GET    /{quotation}/pdf               → download PDF
|
| Admin endpoints (auth:sanctum + admin role):
| - GET    /admin                         → list all quotations
| - GET    /admin/{quotation}             → detail
| - POST   /admin                         → create draft
| - PUT    /admin/{quotation}             → update draft
| - POST   /admin/{quotation}/send        → send to customer
| - POST   /admin/{quotation}/expire      → mark as expired
*/

use App\Http\Controllers\Api\V1\Quotation\QuotationController;
use Illuminate\Support\Facades\Route;

Route::prefix('quotations')->group(function () {
    Route::get('/', [QuotationController::class, 'index'])->name('api.quotations.index');
    Route::get('/{quotation}', [QuotationController::class, 'show'])->name('api.quotations.show');
    Route::post('/{quotation}/accept', [QuotationController::class, 'accept'])->name('api.quotations.accept');
    Route::post('/{quotation}/reject', [QuotationController::class, 'reject'])->name('api.quotations.reject');
    Route::get('/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('api.quotations.pdf');

    Route::prefix('admin')->group(function () {
        Route::get('/', [QuotationController::class, 'adminIndex'])->name('api.quotations.admin.index');
        Route::get('/{quotation}', [QuotationController::class, 'adminShow'])->name('api.quotations.admin.show');
        Route::post('/', [QuotationController::class, 'store'])->name('api.quotations.admin.store');
        Route::put('/{quotation}', [QuotationController::class, 'update'])->name('api.quotations.admin.update');
        Route::post('/{quotation}/send', [QuotationController::class, 'send'])->name('api.quotations.admin.send');
        Route::post('/{quotation}/expire', [QuotationController::class, 'markExpired'])->name('api.quotations.admin.expire');
    });
});