<?php

namespace App\Filament\Admin\Resources\Menus\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'header' => 'info',
                        'footer' => 'success',
                        'sidebar' => 'warning',
                        'mobile' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state)))
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('allItems')
                    ->alignCenter(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->placeholder('—')
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }
}