<?php

namespace App\Filament\Admin\Resources\Payments\Tables;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->label('Payment #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('gateway')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'midtrans' => 'info',
                        'manual_transfer' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state))),

                TextColumn::make('payment_type')
                    ->label('Method')
                    ->formatStateUsing(fn (?string $state) => $state ? str_replace('_', ' ', ucfirst($state)) : '—')
                    ->placeholder('—'),

                TextColumn::make('gross_amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'settlement', 'captured' => 'success',
                        'pending', 'authorized' => 'warning',
                        'denied', 'cancelled', 'expired', 'failed' => 'danger',
                        'refunded', 'partial_refunded' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', strtoupper($state))),

                TextColumn::make('fraud_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'accept' => 'success',
                        'challenge' => 'warning',
                        'deny' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('transaction_id')
                    ->label('Txn ID')
                    ->copyable()
                    ->limit(20)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expired_at')
                    ->label('Expires')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'authorized' => 'Authorized',
                        'captured' => 'Captured',
                        'settlement' => 'Settlement',
                        'denied' => 'Denied',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'partial_refunded' => 'Partial Refunded',
                    ])
                    ->multiple(),

                SelectFilter::make('gateway')
                    ->options([
                        'midtrans' => 'Midtrans',
                        'manual_transfer' => 'Manual Transfer',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('refresh_status')
                    ->label('Refresh from Gateway')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn ($record) => $record->transaction_id && $record->status === 'pending')
                    ->action(function ($record) {
                        try {
                            app(\App\Services\V1\MidtransService::class)->refreshStatusFromGateway($record);
                            \Filament\Notifications\Notification::make()
                                ->title('Status refreshed from Midtrans')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Refresh failed: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}