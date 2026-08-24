<?php

namespace App\Filament\Admin\Resources\SiteSettings\Pages;

use App\Filament\Admin\Resources\SiteSettings\SiteSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteSettings extends ListRecords
{
    protected static string $resource = SiteSettingResource::class;
}