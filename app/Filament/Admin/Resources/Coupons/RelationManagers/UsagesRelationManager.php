<?php

namespace App\Filament\Admin\Resources\Coupons\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $title = 'Riwayat Penggunaan';

    protected static ?string $modelLabel = 'Penggunaan';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money('IDR'),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}