<?php

namespace App\Filament\Admin\Resources\Coupons\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percent' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),

                TextColumn::make('value')
                    ->label('Nilai')
                    ->money('IDR')
                    ->formatStateUsing(fn ($state, $record) => $record?->type === 'percent' ? $state.'%' : 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('min_order_amount')
                    ->label('Min Order')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('max_discount_amount')
                    ->label('Max Discount')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('usage')
                    ->label('Penggunaan')
                    ->state(fn ($record) => $record?->max_uses ? "{$record->used_count} / {$record->max_uses}" : "{$record->used_count} / ∞")
                    ->badge()
                    ->color(fn ($state, $record) => $record?->max_uses && $record->used_count >= $record->max_uses ? 'danger' : 'info'),

                IconColumn::make('is_active')->label('Aktif')->boolean()->toggleable(),

                IconColumn::make('is_first_order_only')
                    ->label('Order Pertama')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->date('d M Y')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percent' => 'Percent',
                        'fixed' => 'Fixed',
                    ]),

                Filter::make('is_active')
                    ->label('Hanya Aktif')
                    ->query(fn (Builder $q) => $q->where('is_active', true)),

                Filter::make('expired')
                    ->label('Sudah Kadaluarsa')
                    ->query(fn (Builder $q) => $q->whereNotNull('expires_at')->where('expires_at', '<', now())),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggle')
                    ->label(fn ($record) => $record?->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record) => $record?->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record?->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        $state = $record->is_active ? 'diaktifkan' : 'dinonaktifkan';
                        Notification::make()->title("Coupon {$state}")->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}