<?php

namespace App\Filament\Admin\Resources\ServiceBookings\Pages;

use App\Filament\Admin\Resources\ServiceBookings\ServiceBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceBookings extends ListRecords
{
    protected static string $resource = ServiceBookingResource::class;
}