<?php

namespace App\Filament\Admin\Resources\Shipments;

use App\Filament\Admin\Resources\Shipments\Pages\ListShipments;
use App\Filament\Admin\Resources\Shipments\Pages\ViewShipment;
use App\Filament\Admin\Resources\Shipments\Tables\ShipmentsTable;
use App\Models\Shipment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    public static function getNavigationGroup(): ?string
    {
        return 'Penjualan';
    }

    protected static ?string $modelLabel = 'Shipment';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'shipments';

    public static function table(Table $table): Table
    {
        return ShipmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShipments::route('/'),
            'view' => ViewShipment::route('/{record}'),
        ];
    }
}