<?php

namespace App\Filament\Admin\Resources\SiteSettings\Pages;

use App\Filament\Admin\Resources\SiteSettings\SiteSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    /**
     * Handle dynamic value field based on type.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // For image type, upload to disk and store URL in value column
        if ($this->record->type === 'image' && ! empty($data['image_value'])) {
            $data['value'] = $data['image_value'];
        }
        unset($data['image_value']);

        // For boolean type, convert 0/1 string
        if ($this->record->type === 'boolean' && isset($data['value'])) {
            $data['value'] = filter_var($data['value'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        // Clear cached settings so they refresh on next request
        \Illuminate\Support\Facades\Cache::forget('site_settings:public:group:');

        return $data;
    }
}