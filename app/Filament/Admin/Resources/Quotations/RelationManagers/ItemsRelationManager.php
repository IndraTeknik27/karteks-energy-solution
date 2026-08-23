<?php

namespace App\Filament\Admin\Resources\Quotations\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    protected static ?string $modelLabel = 'Item';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Item')
                    ->required()
                    ->maxLength(191)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('qty')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1),

                TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->minValue(0),

                Hidden::make('sort')
                    ->default(0),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Item')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->money('IDR'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->weight('bold'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function () {
                        app(\App\Services\V1\QuotationService::class)->recalculateTotals($this->getOwnerRecord());
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function () {
                        app(\App\Services\V1\QuotationService::class)->recalculateTotals($this->getOwnerRecord());
                    }),
                DeleteAction::make()
                    ->after(function () {
                        app(\App\Services\V1\QuotationService::class)->recalculateTotals($this->getOwnerRecord());
                    }),
            ])
            ->defaultSort('sort');
    }
}