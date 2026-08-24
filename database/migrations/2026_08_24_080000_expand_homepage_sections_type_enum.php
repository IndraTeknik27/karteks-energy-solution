<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Expand enum ke section types baru via raw SQL (Laravel change() tidak support ENUM modify di MySQL).
        DB::statement("
            ALTER TABLE homepage_sections
            MODIFY COLUMN type ENUM(
                'hero_banner',
                'featured_categories',
                'featured_products',
                'ev_car',
                'ev_bike',
                'custom_battery_promo',
                'services_grid',
                'testimonials',
                'blog_highlights',
                'brand_logos',
                'custom_html'
            ) NOT NULL DEFAULT 'featured_products'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE homepage_sections
            MODIFY COLUMN type ENUM(
                'hero_banner',
                'featured_products',
                'categories',
                'banners',
                'latest_blogs',
                'testimonials',
                'brands',
                'custom_html'
            ) NOT NULL DEFAULT 'featured_products'
        ");
    }
};