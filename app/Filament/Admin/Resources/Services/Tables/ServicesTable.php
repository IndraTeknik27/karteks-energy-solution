<?php

namespace App\Filament\Admin\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->size(40)
                    ->square(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('pricing_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'success',
                        'starting_price' => 'info',
                        'quotation' => 'warning',
                        'free' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Pasti',
                        'starting_price' => 'Mulai Dari',
                        'quotation' => 'Quotation',
                        'free' => 'Gratis',
                    }),

                TextColumn::make('price_label')
                    ->label('Harga'),

                IconColumn::make('is_featured')->boolean()->toggleable(),
                IconColumn::make('is_active')->boolean(),

                TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->badge()
                    ->color('info')
                    ->label('Bookings'),
            ])
            ->filters([
                SelectFilter::make('pricing_type')
                    ->options([
                        'fixed' => 'Pasti',
                        'starting_price' => 'Mulai Dari',
                        'quotation' => 'Quotation',
                        'free' => 'Gratis',
                    ]),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_featured'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort');
    }
}