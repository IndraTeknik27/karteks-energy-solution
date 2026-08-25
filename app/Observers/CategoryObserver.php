<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\Cache\CatalogSidebarCacheService;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        CatalogSidebarCacheService::flushCategories();
    }

    public function deleted(Category $category): void
    {
        CatalogSidebarCacheService::flushCategories();
    }
}