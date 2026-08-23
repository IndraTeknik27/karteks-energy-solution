<?php

namespace App\Filament\Admin\Resources\CustomBatteryRequests\Pages;

use App\Filament\Admin\Resources\CustomBatteryRequests\CustomBatteryRequestResource;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCustomBatteryRequests extends ListRecords
{
    protected static string $resource = CustomBatteryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}