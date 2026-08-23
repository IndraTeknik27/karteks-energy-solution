<?php

/*
|--------------------------------------------------------------------------
| KARTEKS ENERGY SOLUTION - Business Configuration
|--------------------------------------------------------------------------
|
| Konfigurasi bisnis utama KARTEKS ENERGY SOLUTION.
| File ini terpusat untuk konsistensi konfigurasi lintas modul.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Perusahaan
    |--------------------------------------------------------------------------
    */
    'company' => [
        'name' => env('COMPANY_NAME', 'KARTEKS ENERGY SOLUTION'),
        'legal_name' => 'KARTEKS ENERGY SOLUTION',
        'tagline' => 'Solusi Energi Terbarukan & Kendaraan Listrik',
        'short_name' => 'KARTEKS',
        'email' => env('COMPANY_EMAIL', 'karteksenergy27@gmail.com'),
        'phone' => env('COMPANY_PHONE', '+6281545326426'),
        'whatsapp' => env('COMPANY_WHATSAPP', '+6281545326426'),
        'website' => env('COMPANY_WEBSITE', 'https://www.karteksenergy.com'),
        'address' => env('COMPANY_ADDRESS', 'Jln. Bonto Marannu, Perumahan Tanjung Kencana Residence No. 8, Kabupaten Gowa, Sulawesi Selatan, Indonesia'),
        'province' => 'Sulawesi Selatan',
        'city' => 'Kabupaten Gowa',
        'country' => 'Indonesia',
        'logo' => null, // akan diisi via Filament CMS
        'favicon' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Lokal & Currency
    |--------------------------------------------------------------------------
    */
    'locale' => [
        'default' => 'id',
        'fallback' => 'en',
        'currency' => env('DEFAULT_CURRENCY', 'IDR'),
        'currency_symbol' => 'Rp',
        'currency_format' => 'id_ID',
        'timezone' => env('DEFAULT_TIMEZONE', 'Asia/Makassar'),
        'date_format' => 'd F Y',
        'datetime_format' => 'd F Y H:i',
        'tax_rate' => 11.0, // PPN Indonesia 11%
        'tax_inclusive' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Nomor Order / Invoice / Quotation
    |--------------------------------------------------------------------------
    | Format: {prefix}{Ymd}-{sequence}
    | Contoh: ORD-20260823-00001
    */
    'numbering' => [
        'order' => [
            'prefix' => 'ORD',
            'padding' => 5,
        ],
        'invoice' => [
            'prefix' => 'INV',
            'padding' => 5,
        ],
        'quotation' => [
            'prefix' => 'QTN',
            'padding' => 5,
        ],
        'custom_battery' => [
            'prefix' => 'CBR',
            'padding' => 5,
        ],
        'booking' => [
            'prefix' => 'BKG',
            'padding' => 5,
        ],
        'payment' => [
            'prefix' => 'PAY',
            'padding' => 5,
        ],
        'shipment' => [
            'prefix' => 'SHP',
            'padding' => 5,
        ],
        'coupon' => [
            'prefix' => 'CBN',
            'padding' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Order (enum string values)
    |--------------------------------------------------------------------------
    */
    'order_status' => [
        'draft' => 'DRAFT',
        'pending_payment' => 'PENDING_PAYMENT',
        'payment_pending' => 'PAYMENT_PENDING',
        'paid' => 'PAID',
        'processing' => 'PROCESSING',
        'ready_to_ship' => 'READY_TO_SHIP',
        'shipped' => 'SHIPPED',
        'delivered' => 'DELIVERED',
        'completed' => 'COMPLETED',
        'cancelled' => 'CANCELLED',
        'expired' => 'EXPIRED',
        'refunded' => 'REFUNDED',
        'failed' => 'FAILED',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Payment
    |--------------------------------------------------------------------------
    */
    'payment_status' => [
        'pending' => 'PENDING',
        'authorized' => 'AUTHORIZED',
        'captured' => 'CAPTURED',
        'settlement' => 'SETTLEMENT',
        'denied' => 'DENIED',
        'cancelled' => 'CANCELLED',
        'expired' => 'EXPIRED',
        'failed' => 'FAILED',
        'refunded' => 'REFUNDED',
        'partial_refunded' => 'PARTIAL_REFUNDED',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Quotation
    |--------------------------------------------------------------------------
    */
    'quotation_status' => [
        'draft' => 'DRAFT',
        'sent' => 'SENT',
        'viewed' => 'VIEWED',
        'accepted' => 'ACCEPTED',
        'rejected' => 'REJECTED',
        'expired' => 'EXPIRED',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Custom Battery Request
    |--------------------------------------------------------------------------
    */
    'custom_battery_status' => [
        'submitted' => 'SUBMITTED',
        'under_review' => 'UNDER_REVIEW',
        'revision_requested' => 'REVISION_REQUESTED',
        'quoted' => 'QUOTED',
        'approved' => 'APPROVED',
        'rejected' => 'REJECTED',
        'in_production' => 'IN_PRODUCTION',
        'completed' => 'COMPLETED',
        'cancelled' => 'CANCELLED',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Service Booking
    |--------------------------------------------------------------------------
    */
    'booking_status' => [
        'pending' => 'PENDING',
        'confirmed' => 'CONFIRMED',
        'rescheduled' => 'RESCHEDULED',
        'in_progress' => 'IN_PROGRESS',
        'completed' => 'COMPLETED',
        'cancelled' => 'CANCELLED',
    ],

    /*
    |--------------------------------------------------------------------------
    | Battery Options (untuk Custom Battery Request)
    |--------------------------------------------------------------------------
    */
    'battery_options' => [
        'chemistry' => ['Li-ion', 'LiFePO4', 'Lainnya'],
        'voltage' => ['12V', '24V', '36V', '48V', '60V', '72V', 'Custom'],
        'applications' => [
            'EV Bike' => 'Sepeda Motor Listrik',
            'EV Car' => 'Mobil Listrik',
            'Solar' => 'Solar Energy',
            'Backup Power' => 'Backup Power / UPS',
            'Industrial' => 'Industrial',
            'Custom' => 'Custom / Lainnya',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori Produk Default
    |--------------------------------------------------------------------------
    */
    'default_categories' => [
        ['name' => 'EV Car',           'slug' => 'ev-car',             'icon' => 'car',          'description' => 'Mobil listrik berkualitas tinggi'],
        ['name' => 'EV Bike',          'slug' => 'ev-bike',            'icon' => 'motorcycle',   'description' => 'Sepeda motor listrik performa tinggi'],
        ['name' => 'Custom Battery',   'slug' => 'custom-battery',     'icon' => 'battery',      'description' => 'Battery pack custom sesuai kebutuhan'],
        ['name' => 'Renewable Energy', 'slug' => 'renewable-energy',   'icon' => 'leaf',         'description' => 'Solusi energi terbarukan'],
        ['name' => 'Solar Energy',     'slug' => 'solar-energy',       'icon' => 'sun',          'description' => 'Panel solar & instalasi surya'],
        ['name' => 'Battery Storage',  'slug' => 'battery-storage',    'icon' => 'battery-100',  'description' => 'Sistem penyimpanan energi'],
        ['name' => 'EV Charger',       'slug' => 'ev-charger',         'icon' => 'bolt',         'description' => 'Charger untuk kendaraan listrik'],
        ['name' => 'Accessories',      'slug' => 'accessories',        'icon' => 'wrench',       'description' => 'Aksesoris pendukung'],
        ['name' => 'Spare Part',       'slug' => 'spare-part',         'icon' => 'cog',          'description' => 'Suku cadang original'],
        ['name' => 'Services',         'slug' => 'services',           'icon' => 'wrench-screwdriver', 'description' => 'Layanan jasa profesional'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipe Service / Jasa
    |--------------------------------------------------------------------------
    */
    'service_types' => [
        ['name' => 'Konsultasi EV',           'slug' => 'konsultasi-ev',           'pricing_type' => 'fixed'],
        ['name' => 'Custom Battery',          'slug' => 'custom-battery-service',  'pricing_type' => 'quotation'],
        ['name' => 'Battery Repair',          'slug' => 'battery-repair',          'pricing_type' => 'starting_price'],
        ['name' => 'EV Conversion',           'slug' => 'ev-conversion',           'pricing_type' => 'quotation'],
        ['name' => 'EV Modification',         'slug' => 'ev-modification',         'pricing_type' => 'quotation'],
        ['name' => 'Solar Installation',      'slug' => 'solar-installation',      'pricing_type' => 'quotation'],
        ['name' => 'Solar Maintenance',       'slug' => 'solar-maintenance',       'pricing_type' => 'fixed'],
        ['name' => 'Battery Storage Installation', 'slug' => 'battery-storage-installation', 'pricing_type' => 'quotation'],
        ['name' => 'EV Charger Installation', 'slug' => 'ev-charger-installation', 'pricing_type' => 'starting_price'],
        ['name' => 'Energy Audit',            'slug' => 'energy-audit',            'pricing_type' => 'fixed'],
        ['name' => 'Maintenance & After-sales','slug' => 'maintenance-after-sales', 'pricing_type' => 'fixed'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Type untuk Service
    |--------------------------------------------------------------------------
    */
    'pricing_types' => [
        'fixed' => 'Harga Pasti',
        'starting_price' => 'Harga Mulai Dari',
        'quotation' => 'Berdasarkan Quotation',
        'free' => 'Gratis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stock Configuration
    |--------------------------------------------------------------------------
    */
    'inventory' => [
        'low_stock_threshold' => 5,
        'default_stock_min' => 1,
        'reservation_ttl_minutes' => 60, // reserve stock saat checkout, expire setelah 60 menit
        'cart_ttl_days' => 30, // cart items expire setelah 30 hari
        'allow_backorder' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Configuration
    |--------------------------------------------------------------------------
    */
    'cart' => [
        'tax_rate' => env('CART_TAX_RATE', 0), // 0 = tanpa tax; PPN di-handle terpisah
        'currency' => env('DEFAULT_CURRENCY', 'IDR'),
        'guest_enabled' => true,
        'guest_session_key' => 'karteks_guest_cart',
        'min_order_amount' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Templates Path
    |--------------------------------------------------------------------------
    */
    'email' => [
        'from_address' => env('COMPANY_EMAIL', 'karteksenergy27@gmail.com'),
        'from_name' => env('COMPANY_NAME', 'KARTEKS ENERGY SOLUTION'),
        'reply_to' => env('COMPANY_EMAIL', 'karteksenergy27@gmail.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Configuration
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'business_number' => env('WHATSAPP_BUSINESS_NUMBER', '+6281545326426'),
        'business_number_raw' => '6281545326426',
        'api_provider' => env('WHATSAPP_PROVIDER', 'log'), // log | fonnte | wablas | meta
        'api_token' => env('WHATSAPP_API_TOKEN'),
        'api_url' => env('WHATSAPP_API_URL'),
        'enabled' => env('WHATSAPP_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (Flutter Push Notification)
    |--------------------------------------------------------------------------
    */
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'project_id' => env('FCM_PROJECT_ID'),
        'enabled' => !empty(env('FCM_SERVER_KEY')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Log Configuration
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => true,
        'log_admin_actions' => true,
        'log_customer_actions' => false,
        'retention_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_image_size_kb' => 5120, // 5MB
        'max_file_size_kb' => 10240, // 10MB
        'allowed_image_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'allowed_file_mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
        'image_dimensions' => [
            'product_thumb' => ['width' => 600, 'height' => 600],
            'product_large' => ['width' => 1200, 'height' => 1200],
            'banner' => ['width' => 1920, 'height' => 600],
            'avatar' => ['width' => 400, 'height' => 400],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'version' => 'v1',
        'rate_limit' => [
            'default' => 60, // requests per minute
            'auth' => 10,
            'checkout' => 20,
            'webhook' => 100,
        ],
        'pagination' => [
            'default_per_page' => 15,
            'max_per_page' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'site_name' => 'KARTEKS ENERGY SOLUTION',
        'default_title_suffix' => ' | KARTEKS ENERGY SOLUTION',
        'default_description' => 'Solusi energi terbarukan, kendaraan listrik, custom battery, dan konsultasi profesional dari KARTEKS ENERGY SOLUTION.',
        'default_keywords' => 'karteks, energy solution, EV, electric vehicle, custom battery, solar, renewable energy, Gowa, Sulawesi Selatan',
        'og_image_default' => null,
        'twitter_handle' => '@karteksenergy',
        'schema_type' => 'LocalBusiness',
    ],

];
