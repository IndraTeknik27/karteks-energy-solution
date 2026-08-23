<?php

namespace App\Filament\Admin\Resources\ServiceBookings;

use App\Filament\Admin\Resources\ServiceBookings\Pages\ListServiceBookings;
use App\Filament\Admin\Resources\ServiceBookings\Pages\ViewServiceBooking;
use App\Filament\Admin\Resources\ServiceBookings\Tables\ServiceBookingsTable;
use App\Models\ServiceBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceBookingResource extends Resource
{
    protected static ?string $model = ServiceBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function getNavigationGroup(): ?string
    {
        return 'Jasa & Custom';
    }

    protected static ?string $modelLabel = 'Service Booking';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'service-bookings';

    public static function table(Table $table): Table
    {
        return ServiceBookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceBookings::route('/'),
            'view' => ViewServiceBooking::route('/{record}'),
        ];
    }
}