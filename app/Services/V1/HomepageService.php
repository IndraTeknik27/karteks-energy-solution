<?php

namespace App\Services\V1;

use App\Models\Banner;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomepageService
{
    public function __construct() {}

    /**
     * Load semua section aktif + data per type untuk dirender di homepage.
     *
     * Return array of arrays dengan structure:
     *   [
     *     'section' => HomepageSection,
     *     'view' => string (partial path),
     *     'data' => array (resolved per section type),
     *   ]
     */
    public function loadSections(): array
    {
        $ttl = (int) config('karteks.homepage_sections.cache_ttl', 300);

        // Cache HANYA struktur data (array of arrays), JANGAN cache Eloquent Collections
        // karena unserialize() di request berikutnya gagal (FASE 2.6 gotcha).
        // Section model tetap di-reload fresh per request agar relations (media, dst) hidup.
        $cacheKey = 'homepage:sections:v2';

        $cached = Cache::remember($cacheKey, $ttl, function () {
            $sections = HomepageSection::active()->ordered()->get();

            return $sections
                ->map(function (HomepageSection $section) {
                    $data = $this->resolveSectionData($section);
                    return [
                        'id' => $section->id,
                        'type' => $section->type,
                        'title' => $section->title,
                        'subtitle' => $section->subtitle,
                        'settings' => $section->settings,
                        'data' => $this->normalizeData($data),
                        'view' => config("karteks.homepage_sections.section_partials.{$section->type}"),
                    ];
                })
                ->filter(fn ($row) => $row['data'] !== null && ! empty($row['view']))
                ->values()
                ->all();
        });

        // Hydrate Section model fresh per request (no cache on model)
        return collect($cached)
            ->map(function ($row) {
                $section = HomepageSection::find($row['id']);
                if (! $section) {
                    return null;
                }
                return [
                    'section' => $section,
                    'view' => $row['view'],
                    'data' => $this->rehydrateData($row['type'], $row['data']),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Normalize data untuk cacheable scalars (convert Collection → array of model arrays).
     */
    protected function normalizeData(?array $data): ?array
    {
        if (! $data) {
            return null;
        }
        $out = [];
        foreach ($data as $key => $value) {
            if ($value instanceof \Illuminate\Database\Eloquent\Collection) {
                $out[$key] = $value->map(fn ($model) => $this->modelToArray($model))->all();
            } elseif ($value instanceof \Illuminate\Database\Eloquent\Model) {
                $out[$key] = $this->modelToArray($value);
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * Convert model ke array of key-value untuk serialisasi aman.
     * Reload Eloquent model by ID agar relations (media) hidup di request.
     */
    protected function modelToArray($model): array
    {
        return [
            '__model' => get_class($model),
            'id' => $model->id,
            'attributes' => $model->getAttributes(),
            'relations' => $this->serializeRelations($model),
        ];
    }

    protected function serializeRelations($model): array
    {
        $out = [];
        foreach ($model->getRelations() as $name => $relation) {
            if ($relation instanceof \Illuminate\Database\Eloquent\Model) {
                $out[$name] = $this->modelToArray($relation);
            } elseif ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
                $out[$name] = $relation->map(fn ($r) => $this->modelToArray($r))->all();
            } else {
                $out[$name] = $relation;
            }
        }
        return $out;
    }

    /**
     * Rehydrate data dari cache: cari model by ID, rebuild relations (eager load minimal).
     */
    protected function rehydrateData(string $type, array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[$key] = match ($key) {
                'banners', 'categories', 'products', 'services', 'testimonials', 'blogs', 'brands' => $this->rehydrateCollection($value),
                'category' => $this->rehydrateModel($value),
                default => $value,
            };
        }
        return $out;
    }

    protected function rehydrateCollection(array $items): \Illuminate\Support\Collection
    {
        if (empty($items)) {
            return collect();
        }
        $ids = collect($items)->pluck('id')->all();
        $class = $items[0]['__model'] ?? null;
        if (! $class || ! class_exists($class)) {
            return collect();
        }
        return $class::query()->whereIn('id', $ids)->get();
    }

    protected function rehydrateModel(?array $value): ?\Illuminate\Database\Eloquent\Model
    {
        if (! $value || empty($value['__model'])) {
            return null;
        }
        $class = $value['__model'];
        if (! class_exists($class)) {
            return null;
        }
        return $class::query()->find($value['id']);
    }

    /**
     * Clear cache homepage sections (called dari Filament save).
     */
    public function clearCache(): void
    {
        Cache::forget('homepage:sections:v2');
    }

    /**
     * Reorder sections by array of IDs.
     */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $sort => $id) {
            HomepageSection::where('id', $id)->update(['sort' => ($sort + 1) * 10]);
        }
        $this->clearCache();
    }

    /**
     * Toggle active status section.
     */
    public function toggleActive(HomepageSection $section): HomepageSection
    {
        $section->update(['is_active' => ! $section->is_active]);
        $this->clearCache();
        return $section->fresh();
    }

    /**
     * Resolve data per section type (returns raw data array, no Section model wrapper).
     */
    protected function resolveSectionData(HomepageSection $section): ?array
    {
        return match ($section->type) {
            HomepageSection::TYPE_HERO_BANNER => $this->loadHeroBanners($section),
            HomepageSection::TYPE_FEATURED_CATEGORIES => $this->loadFeaturedCategories($section),
            HomepageSection::TYPE_FEATURED_PRODUCTS => $this->loadFeaturedProducts($section),
            HomepageSection::TYPE_EV_CAR => $this->loadCategoryShowcase($section),
            HomepageSection::TYPE_EV_BIKE => $this->loadCategoryShowcase($section),
            HomepageSection::TYPE_CUSTOM_BATTERY_PROMO => $this->loadCustomBatteryPromo($section),
            HomepageSection::TYPE_SERVICES_GRID => $this->loadServicesGrid($section),
            HomepageSection::TYPE_TESTIMONIALS => $this->loadTestimonials($section),
            HomepageSection::TYPE_BLOG_HIGHLIGHTS => $this->loadBlogHighlights($section),
            HomepageSection::TYPE_BRAND_LOGOS => $this->loadBrandLogos($section),
            HomepageSection::TYPE_CUSTOM_HTML => $this->loadCustomHtml($section),
            default => null,
        };
    }

    protected function loadHeroBanners(HomepageSection $section): array
    {
        $position = $section->getSetting('banner_position', Banner::POSITION_HOME_HERO);
        $maxItems = (int) $section->getSetting('max_items', 5);

        $banners = Banner::active()
            ->position($position)
            ->orderBy('sort')
            ->limit($maxItems)
            ->get();

        return [
            'banners' => $banners,
            'autoplay' => (bool) $section->getSetting('autoplay', true),
        ];
    }

    protected function loadFeaturedCategories(HomepageSection $section): array
    {
        $maxItems = (int) $section->getSetting('max_items', 8);
        $showCount = (bool) $section->getSetting('show_product_count', true);

        $query = Category::query()
            ->active()
            ->roots()
            ->orderBy('sort')
            ->limit($maxItems);

        if ($showCount) {
            $query->withCount(['products' => fn ($q) => $q->where('status', 'published')]);
        }

        return [
            'categories' => $query->get(),
        ];
    }

    protected function loadFeaturedProducts(HomepageSection $section): array
    {
        $maxItems = (int) $section->getSetting('max_items', 8);
        $source = $section->getSetting('source', 'featured');

        $query = Product::query()
            ->where('status', 'published')
            ->with(['category', 'brand'])
            ->limit($maxItems);

        $query = match ($source) {
            'new' => $query->where('is_new_arrival', true)->latest('published_at'),
            'bestseller' => $query->where('is_bestseller', true)->orderByDesc('sales_count'),
            'latest' => $query->latest('published_at'),
            default => $query->where('is_featured', true)->latest('published_at'),
        };

        return [
            'products' => $query->get(),
            'columns' => (int) $section->getSetting('columns', 4),
        ];
    }

    protected function loadCategoryShowcase(HomepageSection $section): array
    {
        $slug = $section->getSetting('category_slug');
        $maxItems = (int) $section->getSetting('max_items', 6);

        $category = $slug ? Category::where('slug', $slug)->active()->first() : null;

        $products = $category
            ? Product::query()
                ->where('status', 'published')
                ->where('category_id', $category->id)
                ->with(['category', 'brand'])
                ->latest('published_at')
                ->limit($maxItems)
                ->get()
            : collect();

        return [
            'category' => $category,
            'products' => $products,
        ];
    }

    protected function loadCustomBatteryPromo(HomepageSection $section): array
    {
        return [
            'cta_url' => $section->getSetting('cta_url', '/dashboard/custom-battery/create'),
            'cta_label' => $section->getSetting('cta_label', 'Konsultasi Sekarang'),
        ];
    }

    protected function loadServicesGrid(HomepageSection $section): array
    {
        $maxItems = (int) $section->getSetting('max_items', 6);
        $featuredOnly = (bool) $section->getSetting('show_only_featured', true);

        $query = Service::query()
            ->active()
            ->with('category')
            ->orderBy('sort')
            ->limit($maxItems);

        if ($featuredOnly) {
            $query->where('is_featured', true);
        }

        return [
            'services' => $query->get(),
        ];
    }

    protected function loadTestimonials(HomepageSection $section): array
    {
        $maxItems = (int) $section->getSetting('max_items', 6);
        $featuredOnly = (bool) $section->getSetting('show_only_featured', true);

        $query = Testimonial::query()
            ->active()
            ->limit($maxItems);

        if ($featuredOnly) {
            $query->orderByDesc('is_featured');
        }

        $query->orderBy('sort');

        return [
            'testimonials' => $query->get(),
        ];
    }

    protected function loadBlogHighlights(HomepageSection $section): array
    {
        $maxItems = (int) $section->getSetting('max_items', 3);
        $featuredOnly = (bool) $section->getSetting('show_only_featured', false);

        $query = Blog::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit($maxItems);

        if ($featuredOnly) {
            $query->where('is_featured', true);
        }

        return [
            'blogs' => $query->get(),
        ];
    }

    protected function loadBrandLogos(HomepageSection $section): array
    {
        $maxItems = (int) $section->getSetting('max_items', 12);

        $query = Brand::query()
            ->active()
            ->orderBy('sort')
            ->limit($maxItems);

        return [
            'brands' => $query->get(),
        ];
    }

    protected function loadCustomHtml(HomepageSection $section): array
    {
        return [
            'html' => (string) $section->getSetting('html', ''),
        ];
    }

    /**
     * Get banner by position (untuk partial non-section, mis. sidebar widget).
     */
    public function getBannersByPosition(string $position, int $limit = 5): Collection
    {
        return Banner::active()
            ->position($position)
            ->orderBy('sort')
            ->limit($limit)
            ->get();
    }
}