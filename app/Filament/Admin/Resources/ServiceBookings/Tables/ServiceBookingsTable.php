<?php

namespace App\Filament\Admin\Resources\ServiceBookings\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->label('Booking #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->suffix(' min')
                    ->placeholder('—'),

                TextColumn::make('location_type')
                    ->label('Lokasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'on_site' => 'info',
                        'in_store' => 'success',
                        'remote' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on_site' => 'On-site',
                        'in_store' => 'In-store',
                        'remote' => 'Remote',
                        default => $state,
                    }),

                TextColumn::make('estimated_cost')
                    ->label('Est. Cost')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('final_cost')
                    ->label('Final')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'rescheduled' => 'warning',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'rescheduled' => 'Rescheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                Filter::make('upcoming')
                    ->label('Mendatang')
                    ->query(fn (Builder $q) => $q->upcoming()),

                Filter::make('today')
                    ->label('Hari ini')
                    ->query(fn (Builder $q) => $q->whereDate('scheduled_at', now()->toDateString())),

                Filter::make('this_week')
                    ->label('Minggu ini')
                    ->query(fn (Builder $q) => $q->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('scheduled_at', 'desc');
    }
}