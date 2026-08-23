<?php

/*
|--------------------------------------------------------------------------
| Midtrans Payment Gateway Configuration
|--------------------------------------------------------------------------
|
| Konfigurasi untuk integrasi Midtrans Snap & Core API.
|
| ENV: sandbox | production
|
| Sandbox URL:
|   Snap JS  : https://app.sandbox.midtrans.com/snap/snap.js
|   Snap API : https://app.sandbox.midtrans.com/snap/v1/transactions
|   Core API : https://api.sandbox.midtrans.com/v2
|
| Production URL:
|   Snap JS  : https://app.midtrans.com/snap/snap.js
|   Snap API : https://app.midtrans.com/snap/v1/transactions
|   Core API : https://api.midtrans.com/v2
|
*/

return [

    'environment' => env('MIDTRANS_ENV', 'sandbox'),

    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),

    'server_key' => env('MIDTRANS_SERVER_KEY'),

    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    // URLs based on environment
    'snap_url' => env('MIDTRANS_ENV', 'sandbox') === 'production'
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    'snap_api_url' => env('MIDTRANS_ENV', 'sandbox') === 'production'
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'api_url' => env('MIDTRANS_ENV', 'sandbox') === 'production'
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',

    // Payment notification & redirect URLs
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL'),
    'finish_url' => env('MIDTRANS_FINISH_URL'),
    'unfinish_url' => env('MIDTRANS_UNFINISH_URL'),
    'error_url' => env('MIDTRANS_ERROR_URL'),

    // Payment method whitelist (kosongkan untuk izinkan semua)
    'enabled_payments' => [
        // 'credit_card', 'bca_va', 'bni_va', 'bri_va', 'echannel',
        // 'permata_va', 'other_va', 'gopay', 'shopeepay',
        // 'qris', 'indosat_dompetku', 'danamon_online',
    ],

    // Transaction expiry (in minutes)
    'expiry' => [
        'default' => 60, // 1 jam untuk transaksi umum
        'snap_token' => 60,
    ],

    // Custom expiry per payment type (in minutes)
    'custom_expiry' => [
        // 'bca_va' => 60,
        // 'bni_va' => 60,
    ],

    // Signature key validation (SHA512 dari order_id + status_code + gross_amount + server_key)
    'validate_signature' => true,

    // Idempotency window (jam) - notification yang sama dalam window ini akan di-skip
    'idempotency_window_hours' => 24,

    // Logging - log semua raw response Midtrans ke storage/logs/midtrans.log
    'logging' => [
        'enabled' => true,
        'channel' => 'midtrans',
    ],

    // 3DS / Secure authentication for credit card
    'secure' => [
        'enabled' => true,
        'acquiring_bank' => 'bca',
        'channel' => 'migs',
    ],

];
