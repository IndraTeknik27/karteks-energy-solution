<?php

namespace App\Filament\Admin\Widgets;

use App\Services\V1\DashboardService;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CustomBatteryStatsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = '🔋 Custom Battery & Quotation';

    protected static ?string $description = 'Pending requests + booking minggu ini';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\CustomBatteryRequest::query()
                ->with('customer:id,name,email')
                ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
                ->latest('created_at')
                ->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('No. Request')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('Guest')
                    ->description(fn ($record) => $record->customer?->email),

                Tables\Columns\TextColumn::make('application')
                    ->label('Aplikasi')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('chemistry')
                    ->label('Kimia')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('voltage')
                    ->label('Voltage')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'warning',
                        'under_review' => 'info',
                        'revision_requested' => 'danger',
                        'quoted' => 'primary',
                        'approved' => 'success',
                        'in_production' => 'info',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state))),

                Tables\Columns\TextColumn::make('estimated_price')
                    ->label('Est. Price')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => \App\Filament\Admin\Resources\CustomBatteryRequests\CustomBatteryRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak Ada Request Aktif')
            ->emptyStateDescription('Semua custom battery requests sudah selesai/dibatalkan.');
    }
}