<?php

namespace App\Filament\Admin\Resources\HomepageSections\Pages;

use App\Filament\Admin\Resources\HomepageSections\HomepageSectionResource;
use Filament\Resources\Pages\EditRecord;

class EditHomepageSection extends EditRecord
{
    protected static string $resource = HomepageSectionResource::class;

    protected function afterSave(): void
    {
        app(\App\Services\V1\HomepageService::class)->clearCache();
    }
}