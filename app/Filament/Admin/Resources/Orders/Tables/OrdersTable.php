<?php

namespace App\Filament\Admin\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'customer_email', 'customer_phone'])
                    ->description(fn (Order $r) => $r->customer_email),

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->badge()
                    ->color('info'),

                TextColumn::make('total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'midtrans' => 'info',
                        'manual_transfer' => 'warning',
                        'cod' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_payment', 'payment_pending' => 'warning',
                        'paid' => 'info',
                        'processing' => 'primary',
                        'ready_to_ship' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'danger',
                        'refunded' => 'danger',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper(str_replace('_', ' ', $state))),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('shipping_courier')
                    ->label('Kurir')
                    ->formatStateUsing(fn (?string $s) => $s ? strtoupper($s) : '—')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('shipping_tracking_number')
                    ->label('Resi')
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'payment_pending' => 'Payment Pending',
                        'paid' => 'Paid',
                        'processing' => 'Processing',
                        'ready_to_ship' => 'Ready to Ship',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                        'refunded' => 'Refunded',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),

                SelectFilter::make('payment_method')
                    ->options([
                        'midtrans' => 'Midtrans',
                        'manual_transfer' => 'Manual Transfer',
                        'cod' => 'COD',
                    ]),

                Filter::make('unpaid')
                    ->label('Belum Bayar')
                    ->query(fn (Builder $q) => $q->whereNull('paid_at')->whereIn('status', ['pending_payment', 'payment_pending'])),

                Filter::make('in_progress')
                    ->label('Sedang Diproses')
                    ->query(fn (Builder $q) => $q->whereIn('status', ['paid', 'processing', 'ready_to_ship'])),

                Filter::make('today')
                    ->label('Hari ini')
                    ->query(fn (Builder $q) => $q->whereDate('created_at', today())),

                Filter::make('this_month')
                    ->label('Bulan ini')
                    ->query(fn (Builder $q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }
}