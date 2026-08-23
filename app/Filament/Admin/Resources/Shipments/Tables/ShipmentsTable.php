<?php

namespace App\Filament\Admin\Resources\Shipments\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shipment_number')
                    ->label('Shipment #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('courier_name')
                    ->label('Kurir')
                    ->formatStateUsing(fn (?string $s) => $s ? strtoupper($s) : '—')
                    ->placeholder('—'),

                TextColumn::make('courier_service')
                    ->label('Service')
                    ->placeholder('—'),

                TextColumn::make('tracking_number')
                    ->label('No. Resi')
                    ->placeholder('—')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('weight')
                    ->label('Berat')
                    ->suffix(' kg')
                    ->placeholder('—')
                    ->numeric(2),

                TextColumn::make('cost')
                    ->label('Ongkir')
                    ->money('IDR')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'packed' => 'info',
                        'shipped' => 'primary',
                        'in_transit' => 'primary',
                        'delivered' => 'success',
                        'returned' => 'danger',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),

                TextColumn::make('shipped_at')
                    ->label('Shipped')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextColumn::make('delivered_at')
                    ->label('Delivered')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

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
                        'packed' => 'Packed',
                        'shipped' => 'Shipped',
                        'in_transit' => 'In Transit',
                        'delivered' => 'Delivered',
                        'returned' => 'Returned',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                SelectFilter::make('courier_code')
                    ->options([
                        'jne' => 'JNE',
                        'pos' => 'POS',
                        'tiki' => 'TIKI',
                        'sicepat' => 'SiCepat',
                        'jnt' => 'J&T',
                    ]),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }
}