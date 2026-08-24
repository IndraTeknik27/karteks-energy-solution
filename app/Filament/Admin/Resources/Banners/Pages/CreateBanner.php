<?php

namespace App\Filament\Admin\Resources\Banners\Pages;

use App\Filament\Admin\Resources\Banners\BannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    protected function afterCreate(): void
    {
        app(\App\Services\V1\HomepageService::class)->clearCache();
    }
}