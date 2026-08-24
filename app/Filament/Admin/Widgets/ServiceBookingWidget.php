<?php

namespace App\Filament\Admin\Widgets;

use App\Services\V1\DashboardService;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ServiceBookingWidget extends BaseWidget
{
    protected static ?string $heading = '📅 Service Booking';

    protected static ?string $description = 'Booking 7 hari ke depan + statistik';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\ServiceBooking::query()
                ->with(['service', 'customer:id,name', 'technician:id,name'])
                ->whereBetween('scheduled_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->orderBy('scheduled_at')
                ->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('booking_number')
                    ->label('No. Booking')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->placeholder('—')
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->placeholder('Belum')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'rescheduled' => 'info',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state))),

                Tables\Columns\TextColumn::make('location_type')
                    ->label('Lokasi')
                    ->badge()
                    ->color(fn (?string $state) => $state === 'onsite' ? 'warning' : 'info')
                    ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : '—'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak Ada Booking')
            ->emptyStateDescription('Tidak ada booking dalam 7 hari ke depan.');
    }
}