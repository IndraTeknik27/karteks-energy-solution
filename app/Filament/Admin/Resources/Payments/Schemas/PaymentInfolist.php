<?php

namespace App\Filament\Admin\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('payment_number')
                    ->label('Payment Number')
                    ->copyable()
                    ->weight('bold'),

                TextEntry::make('order.order_number')
                    ->label('Order Number')
                    ->copyable(),

                TextEntry::make('order.customer_name')
                    ->label('Customer')
                    ->placeholder('—'),

                TextEntry::make('order.customer_email')
                    ->label('Email')
                    ->copyable()
                    ->placeholder('—'),

                TextEntry::make('gateway')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state))),

                TextEntry::make('payment_type')
                    ->label('Payment Type')
                    ->formatStateUsing(fn (?string $state) => $state ? str_replace('_', ' ', ucfirst($state)) : '—')
                    ->placeholder('—'),

                TextEntry::make('bank')
                    ->placeholder('—'),

                TextEntry::make('va_number')
                    ->label('VA Number')
                    ->copyable()
                    ->placeholder('—'),

                TextEntry::make('gross_amount')
                    ->label('Gross Amount')
                    ->money('IDR'),

                TextEntry::make('fee_amount')
                    ->label('Fee')
                    ->money('IDR'),

                TextEntry::make('net_amount')
                    ->label('Net Amount')
                    ->money('IDR')
                    ->placeholder('—'),

                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'settlement', 'captured' => 'success',
                        'pending', 'authorized' => 'warning',
                        'denied', 'cancelled', 'expired', 'failed' => 'danger',
                        'refunded', 'partial_refunded' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),

                TextEntry::make('fraud_status')
                    ->badge()
                    ->placeholder('—'),

                TextEntry::make('transaction_id')
                    ->label('Transaction ID')
                    ->copyable()
                    ->placeholder('—'),

                TextEntry::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d F Y, H:i')
                    ->placeholder('—'),

                TextEntry::make('expired_at')
                    ->label('Expires At')
                    ->dateTime('d F Y, H:i')
                    ->placeholder('—'),

                TextEntry::make('redirect_url')
                    ->label('Redirect URL')
                    ->copyable()
                    ->placeholder('—')
                    ->columnSpanFull(),
            ]);
    }
}