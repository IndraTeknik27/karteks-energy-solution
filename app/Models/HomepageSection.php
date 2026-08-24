<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'title', 'subtitle', 'type',
        'settings', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public const TYPE_HERO_BANNER = 'hero_banner';
    public const TYPE_FEATURED_CATEGORIES = 'featured_categories';
    public const TYPE_FEATURED_PRODUCTS = 'featured_products';
    public const TYPE_EV_CAR = 'ev_car';
    public const TYPE_EV_BIKE = 'ev_bike';
    public const TYPE_CUSTOM_BATTERY_PROMO = 'custom_battery_promo';
    public const TYPE_SERVICES_GRID = 'services_grid';
    public const TYPE_TESTIMONIALS = 'testimonials';
    public const TYPE_BLOG_HIGHLIGHTS = 'blog_highlights';
    public const TYPE_BRAND_LOGOS = 'brand_logos';
    public const TYPE_CUSTOM_HTML = 'custom_html';

    /**
     * Daftar tipe section + label + default settings untuk seeder.
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_HERO_BANNER => [
                'label' => 'Hero Banner Carousel',
                'icon' => 'photo',
                'description' => 'Banner carousel utama di atas homepage',
                'defaults' => ['banner_position' => 'home_hero', 'max_items' => 5, 'autoplay' => true],
            ],
            self::TYPE_FEATURED_CATEGORIES => [
                'label' => 'Featured Categories',
                'icon' => 'rectangle-stack',
                'description' => 'Grid kategori produk pilihan',
                'defaults' => ['max_items' => 8, 'show_product_count' => true],
            ],
            self::TYPE_FEATURED_PRODUCTS => [
                'label' => 'Featured Products',
                'icon' => 'star',
                'description' => 'Produk pilihan / unggulan',
                'defaults' => ['max_items' => 8, 'source' => 'featured', 'columns' => 4],
            ],
            self::TYPE_EV_CAR => [
                'label' => 'EV Car Showcase',
                'icon' => 'truck',
                'description' => 'Showcase produk Mobil Listrik',
                'defaults' => ['category_slug' => 'ev-car', 'max_items' => 6],
            ],
            self::TYPE_EV_BIKE => [
                'label' => 'EV Bike Showcase',
                'icon' => 'bolt',
                'description' => 'Showcase produk Motor Listrik',
                'defaults' => ['category_slug' => 'ev-bike', 'max_items' => 6],
            ],
            self::TYPE_CUSTOM_BATTERY_PROMO => [
                'label' => 'Custom Battery Promo',
                'icon' => 'battery-50',
                'description' => 'Banner promo layanan custom battery',
                'defaults' => ['cta_url' => '/dashboard/custom-battery/create', 'cta_label' => 'Konsultasi Sekarang'],
            ],
            self::TYPE_SERVICES_GRID => [
                'label' => 'Services Grid',
                'icon' => 'wrench-screwdriver',
                'description' => 'Grid layanan jasa profesional',
                'defaults' => ['max_items' => 6, 'show_only_featured' => true],
            ],
            self::TYPE_TESTIMONIALS => [
                'label' => 'Testimoni Pelanggan',
                'icon' => 'chat-bubble-left-right',
                'description' => 'Testimonial pelanggan',
                'defaults' => ['max_items' => 6, 'show_only_featured' => true],
            ],
            self::TYPE_BLOG_HIGHLIGHTS => [
                'label' => 'Blog Highlights',
                'icon' => 'newspaper',
                'description' => 'Blog/artikel terbaru',
                'defaults' => ['max_items' => 3, 'show_only_featured' => false],
            ],
            self::TYPE_BRAND_LOGOS => [
                'label' => 'Brand Logos',
                'icon' => 'building-storefront',
                'description' => 'Logo-brand partner',
                'defaults' => ['max_items' => 12, 'show_only_active' => true],
            ],
            self::TYPE_CUSTOM_HTML => [
                'label' => 'Custom HTML',
                'icon' => 'code-bracket',
                'description' => 'Custom HTML block (raw HTML)',
                'defaults' => ['html' => '<div class="container mx-auto py-8"><p>Custom HTML</p></div>'],
            ],
        ];
    }

    /**
     * Label untuk tipe section.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type]['label'] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Get a settings value dengan fallback ke default per type.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $value = $this->settings[$key] ?? null;
        if ($value !== null) {
            return $value;
        }

        $typeDefaults = self::typeOptions()[$this->type]['defaults'] ?? [];

        return $typeDefaults[$key] ?? $default;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }
}