<?php

namespace App\Filament\Admin\Resources\Shipments\Pages;

use App\Filament\Admin\Resources\Shipments\ShipmentResource;
use Filament\Resources\Pages\ListRecords;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentResource::class;
}