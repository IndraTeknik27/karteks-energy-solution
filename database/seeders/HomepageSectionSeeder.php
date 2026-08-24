<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'key' => 'hero_banner',
                'title' => 'Selamat Datang di KARTEKS',
                'subtitle' => 'Solusi Energi Terbarukan & Kendaraan Listrik',
                'type' => HomepageSection::TYPE_HERO_BANNER,
                'settings' => [
                    'banner_position' => 'home_hero',
                    'max_items' => 5,
                    'autoplay' => true,
                ],
                'sort' => 10,
                'is_active' => true,
            ],
            [
                'key' => 'featured_categories',
                'title' => 'Kategori Produk',
                'subtitle' => 'Jelajahi produk sesuai kebutuhan Anda',
                'type' => HomepageSection::TYPE_FEATURED_CATEGORIES,
                'settings' => [
                    'max_items' => 8,
                    'show_product_count' => true,
                ],
                'sort' => 20,
                'is_active' => true,
            ],
            [
                'key' => 'featured_products',
                'title' => 'Produk Pilihan',
                'subtitle' => 'Unggulan',
                'type' => HomepageSection::TYPE_FEATURED_PRODUCTS,
                'settings' => [
                    'max_items' => 8,
                    'source' => 'featured',
                    'columns' => 4,
                ],
                'sort' => 30,
                'is_active' => true,
            ],
            [
                'key' => 'ev_car',
                'title' => 'Mobil Listrik Pilihan',
                'subtitle' => 'EV Car',
                'type' => HomepageSection::TYPE_EV_CAR,
                'settings' => [
                    'category_slug' => 'ev-car',
                    'max_items' => 6,
                ],
                'sort' => 40,
                'is_active' => true,
            ],
            [
                'key' => 'ev_bike',
                'title' => 'Motor Listrik Unggulan',
                'subtitle' => 'EV Bike',
                'type' => HomepageSection::TYPE_EV_BIKE,
                'settings' => [
                    'category_slug' => 'ev-bike',
                    'max_items' => 6,
                ],
                'sort' => 50,
                'is_active' => true,
            ],
            [
                'key' => 'custom_battery_promo',
                'title' => 'Custom Battery untuk Kebutuhan Anda',
                'subtitle' => 'Battery pack sesuai spek Anda, dibuat oleh teknisi ahli KARTEKS.',
                'type' => HomepageSection::TYPE_CUSTOM_BATTERY_PROMO,
                'settings' => [
                    'cta_url' => '/dashboard/custom-battery/create',
                    'cta_label' => 'Konsultasi Sekarang',
                ],
                'sort' => 60,
                'is_active' => true,
            ],
            [
                'key' => 'services_grid',
                'title' => 'Layanan Profesional',
                'subtitle' => 'Layanan',
                'type' => HomepageSection::TYPE_SERVICES_GRID,
                'settings' => [
                    'max_items' => 6,
                    'show_only_featured' => true,
                ],
                'sort' => 70,
                'is_active' => true,
            ],
            [
                'key' => 'testimonials',
                'title' => 'Apa Kata Pelanggan Kami',
                'subtitle' => 'Testimoni',
                'type' => HomepageSection::TYPE_TESTIMONIALS,
                'settings' => [
                    'max_items' => 6,
                    'show_only_featured' => true,
                ],
                'sort' => 80,
                'is_active' => true,
            ],
            [
                'key' => 'blog_highlights',
                'title' => 'Tips & Berita Terbaru',
                'subtitle' => 'Blog',
                'type' => HomepageSection::TYPE_BLOG_HIGHLIGHTS,
                'settings' => [
                    'max_items' => 3,
                    'show_only_featured' => false,
                ],
                'sort' => 90,
                'is_active' => true,
            ],
            [
                'key' => 'brand_logos',
                'title' => 'Brand Partner Kami',
                'subtitle' => null,
                'type' => HomepageSection::TYPE_BRAND_LOGOS,
                'settings' => [
                    'max_items' => 12,
                    'show_only_active' => true,
                ],
                'sort' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::updateOrCreate(
                ['key' => $section['key']],
                $section,
            );
        }

        $this->command?->info(sprintf('Seeded %d homepage sections.', count($sections)));
    }
}