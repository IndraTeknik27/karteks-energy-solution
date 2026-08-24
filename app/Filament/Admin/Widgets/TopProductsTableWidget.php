<?php

namespace App\Filament\Admin\Widgets;

use App\Services\V1\DashboardService;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsTableWidget extends BaseWidget
{
    protected static ?string $heading = '🏆 Top Products';

    protected static ?string $description = 'Produk terlaris 30 hari terakhir';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\Product::query()
                ->where('status', 'published')
                ->orderByDesc('sales_count')
                ->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->formatStateUsing(fn ($rowLoop) => ($rowLoop->iteration ?? 0))
                    ->alignCenter()
                    ->width(40),

                Tables\Columns\ImageColumn::make('featured_image_url')
                    ->label('Foto')
                    ->height(32)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(40),

                Tables\Columns\TextColumn::make('effective_price')
                    ->label('Harga')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('sales_count')
                    ->label('Terjual')
                    ->numeric()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 10 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating_avg')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state ? sprintf('★%.1f', (float) $state) : '—')
                    ->color(fn ($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'gray'))
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => \App\Filament\Admin\Resources\Products\ProductResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum Ada Data')
            ->emptyStateDescription('Belum ada order paid dalam 30 hari terakhir.');
    }
}