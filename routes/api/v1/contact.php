<?php

use App\Http\Controllers\Api\V1\Contact\ContactController;
use App\Http\Controllers\Api\V1\Newsletter\NewsletterController;
use Illuminate\Support\Facades\Route;

/*
| FASE 4.5: Public Contact + Newsletter endpoints (no auth)
*/

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware(['throttle:contact', 'file.validate:jpg,jpeg,png,pdf,doc,docx,2048'])
    ->name('contact.store');

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:newsletter')
    ->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');