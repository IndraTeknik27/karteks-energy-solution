<?php

namespace App\Filament\Admin\Widgets;

use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockTableWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = '⚠️ Stok Menipis';

    protected static ?string $description = 'Produk dengan stock di bawah threshold';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\Product::query()
                ->where('status', 'published')
                ->where('manage_stock', true)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereNotNull('low_stock_threshold')
                            ->whereColumn('stock_qty', '<=', 'low_stock_threshold');
                    })->orWhere('stock_qty', '<=', 0);
                })
                ->orderBy('stock_qty', 'asc')
                ->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(40),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->fontFamily('mono')
                    ->size(\Filament\Support\Enums\TextSize::Small)
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('stock_qty')
                    ->label('Stok')
                    ->numeric()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->stock_qty <= 0 => 'danger',
                        $record->stock_qty <= (int) ($record->low_stock_threshold / 2) => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label('Threshold')
                    ->numeric()
                    ->placeholder('—')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('effective_price')
                    ->label('Harga')
                    ->money('IDR')
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => \App\Filament\Admin\Resources\Products\ProductResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('✓ Stok Aman')
            ->emptyStateDescription('Semua produk memiliki stok di atas threshold minimum.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}