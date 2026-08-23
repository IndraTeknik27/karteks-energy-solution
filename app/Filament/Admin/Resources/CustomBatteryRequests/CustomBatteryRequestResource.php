<?php

namespace App\Filament\Admin\Resources\CustomBatteryRequests;

use App\Filament\Admin\Resources\CustomBatteryRequests\Pages\ListCustomBatteryRequests;
use App\Filament\Admin\Resources\CustomBatteryRequests\Pages\ViewCustomBatteryRequest;
use App\Filament\Admin\Resources\CustomBatteryRequests\Tables\CustomBatteryRequestsTable;
use App\Models\CustomBatteryRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomBatteryRequestResource extends Resource
{
    protected static ?string $model = CustomBatteryRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBattery50;

    public static function getNavigationGroup(): ?string
    {
        return 'Jasa & Custom';
    }

    protected static ?string $modelLabel = 'Custom Battery Request';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'custom-battery-requests';

    public static function table(Table $table): Table
    {
        return CustomBatteryRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomBatteryRequests::route('/'),
            'view' => ViewCustomBatteryRequest::route('/{record}'),
        ];
    }
}