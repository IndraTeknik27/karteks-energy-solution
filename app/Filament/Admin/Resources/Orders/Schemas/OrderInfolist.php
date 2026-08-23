<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(2)
                    ->components([
                        TextEntry::make('order_number')->label('Order #')->copyable()->weight('bold'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending_payment', 'payment_pending' => 'warning',
                                'paid' => 'info',
                                'processing' => 'primary',
                                'ready_to_ship' => 'info',
                                'shipped' => 'primary',
                                'delivered', 'completed' => 'success',
                                'cancelled', 'expired', 'refunded', 'failed' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),
                        TextEntry::make('customer_name')->label('Nama'),
                        TextEntry::make('customer_email')->label('Email')->copyable(),
                        TextEntry::make('customer_phone')->label('Phone')->copyable(),
                    ]),

                Section::make('Pembayaran')
                    ->columns(3)
                    ->components([
                        TextEntry::make('total')->label('Total')->money('IDR'),
                        TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                        TextEntry::make('shipping_cost')->label('Ongkir')->money('IDR'),
                        TextEntry::make('coupon_code')->label('Kupon')->placeholder('—'),
                        TextEntry::make('coupon_discount')->label('Diskon')->money('IDR')->placeholder('—'),
                        TextEntry::make('payment_method')
                            ->label('Metode')
                            ->formatStateUsing(fn (string $s) => strtoupper(str_replace('_', ' ', $s))),
                        TextEntry::make('paid_at')->label('Paid At')->dateTime('d M Y H:i')->placeholder('—'),
                    ]),

                Section::make('Pengiriman')
                    ->columns(2)
                    ->components([
                        TextEntry::make('shipping_courier')->label('Kurir')->formatStateUsing(fn (?string $s) => $s ? strtoupper($s) : '—')->placeholder('—'),
                        TextEntry::make('shipping_service')->label('Service')->placeholder('—'),
                        TextEntry::make('shipping_tracking_number')->label('No. Resi')->copyable()->placeholder('—'),
                        TextEntry::make('shipped_at')->label('Shipped')->dateTime('d M Y H:i')->placeholder('—'),
                        TextEntry::make('delivered_at')->label('Delivered')->dateTime('d M Y H:i')->placeholder('—'),
                        TextEntry::make('completed_at')->label('Completed')->dateTime('d M Y H:i')->placeholder('—'),
                    ]),

                Section::make('Customer Notes')
                    ->components([
                        TextEntry::make('customer_notes')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('admin_notes')->label('Admin Notes')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}