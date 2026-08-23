<?php

namespace App\Filament\Admin\Resources\CustomBatteryRequests\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomBatteryRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chemistry')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'LiFePO4' => 'success',
                        'Li-ion' => 'info',
                        default => 'gray',
                    })
                    ->placeholder('—'),

                TextColumn::make('voltage')
                    ->badge()
                    ->color('info'),

                TextColumn::make('application')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state
                        ? (config('karteks.battery_options.applications.'.$state) ?? $state)
                        : '—')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->numeric()
                    ->suffix(' unit'),

                TextColumn::make('estimated_price')
                    ->label('Est. Price')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        'revision_requested' => 'warning',
                        'quoted' => 'primary',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'in_production' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),

                TextColumn::make('revision_count')
                    ->label('Rev')
                    ->badge()
                    ->color('warning')
                    ->placeholder('0')
                    ->toggleable(),

                TextColumn::make('files_count')
                    ->counts('files')
                    ->label('Files')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('deadline')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'revision_requested' => 'Revision Requested',
                        'quoted' => 'Quoted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'in_production' => 'In Production',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                SelectFilter::make('chemistry')
                    ->options(fn () => array_combine(
                        config('karteks.battery_options.chemistry', []),
                        config('karteks.battery_options.chemistry', [])
                    )),

                SelectFilter::make('application')
                    ->options(fn () => config('karteks.battery_options.applications', [])),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }
}