<?php

namespace App\Observers;

use App\Models\Brand;
use App\Services\Cache\CatalogSidebarCacheService;

class BrandObserver
{
    public function saved(Brand $brand): void
    {
        CatalogSidebarCacheService::flushBrands();
    }

    public function deleted(Brand $brand): void
    {
        CatalogSidebarCacheService::flushBrands();
    }
}