<?php

namespace App\Services\Cache;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

/**
 * Cache service untuk catalog sidebar data (categories + brands tree).
 *
 * Why: di setiap /products request, CatalogController query 2 tabel (categories, brands)
 * yang sangat jarang berubah. Cache selama 1 jam + invalidate on write via observers.
 *
 * Pattern: cache ARRAY of arrays (scalars only). Jangan cache Eloquent Collection
 * langsung karena unserialize() di request berikutnya gagal dengan fatal error
 * (FASE 2.6 gotcha) — terutama kalau model berubah schema.
 *
 * Invalidation: dipanggil dari CategoryObserver + BrandObserver (lihat app/Observers).
 */
class CatalogSidebarCacheService
{
    public const CACHE_KEY_CATEGORIES = 'catalog:sidebar:categories:v1';
    public const CACHE_KEY_BRANDS = 'catalog:sidebar:brands:v1';
    public const TTL_SECONDS = 3600; // 1 jam

    /**
     * Get full category tree untuk sidebar/footer.
     *
     * @return array<int, array{id:int,name:string,slug:string,parent_id:?int,sort:int,is_active:bool}>
     */
    public static function getCategories(): array
    {
        return Cache::remember(self::CACHE_KEY_CATEGORIES, self::TTL_SECONDS, function () {
            return Category::query()
                ->active()
                ->orderBy('sort')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'parent_id', 'sort', 'is_active'])
                ->map(fn ($cat) => [
                    'id' => (int) $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'parent_id' => $cat->parent_id !== null ? (int) $cat->parent_id : null,
                    'sort' => (int) $cat->sort,
                    'is_active' => (bool) $cat->is_active,
                ])
                ->all();
        });
    }

    /**
     * Get active brands untuk sidebar filter.
     *
     * @return array<int, array{id:int,name:string,slug:string,sort:int}>
     */
    public static function getBrands(): array
    {
        return Cache::remember(self::CACHE_KEY_BRANDS, self::TTL_SECONDS, function () {
            return Brand::query()
                ->active()
                ->orderBy('sort')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'sort'])
                ->map(fn ($brand) => [
                    'id' => (int) $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'sort' => (int) $brand->sort,
                ])
                ->all();
        });
    }

    /**
     * Invalidate both caches. Called by observers on save/delete.
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY_CATEGORIES);
        Cache::forget(self::CACHE_KEY_BRANDS);
    }

    /**
     * Invalidate categories only.
     */
    public static function flushCategories(): void
    {
        Cache::forget(self::CACHE_KEY_CATEGORIES);
    }

    /**
     * Invalidate brands only.
     */
    public static function flushBrands(): void
    {
        Cache::forget(self::CACHE_KEY_BRANDS);
    }
}
