<?php

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Filament\Admin\Resources\Orders\Schemas\OrderInfolist;
use App\Services\V1\OrderService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $service = app(OrderService::class);
        $record = $this->getRecord();

        return [
            Action::make('confirmPayment')
                ->label('Konfirmasi Pembayaran')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => is_null($record->paid_at) && in_array($record->status, ['pending_payment', 'payment_pending'], true))
                ->form([
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(2)
                        ->default('Pembayaran dikonfirmasi manual via transfer bank'),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->confirmPaymentByAdmin($record, Auth::user(), $data['notes'] ?? null);
                        Notification::make()->title('Pembayaran dikonfirmasi')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('process')
                ->label('Proses Order')
                ->icon('heroicon-o-cog')
                ->color('primary')
                ->visible(fn () => in_array($record->status, ['pending_payment', 'payment_pending', 'paid'], true) && ! $record->paid_at === false || in_array($record->status, ['paid'], true))
                ->form([
                    Textarea::make('notes')->rows(2),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->adminTransition($record, 'processing', Auth::user(), $data['notes'] ?? null);
                        Notification::make()->title('Order diproses')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('readyToShip')
                ->label('Siap Dikirim')
                ->icon('heroicon-o-archive-box')
                ->color('info')
                ->visible(fn () => in_array($record->status, ['paid', 'processing'], true))
                ->form([Textarea::make('notes')->rows(2)])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->adminTransition($record, 'ready_to_ship', Auth::user(), $data['notes'] ?? null);
                        Notification::make()->title('Order siap kirim')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('ship')
                ->label('Kirim')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->visible(fn () => $record->status === 'ready_to_ship')
                ->form([
                    TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->default(fn () => strtoupper(Str::random(12)))
                        ->required()
                        ->maxLength(50),
                    Textarea::make('notes')
                        ->label('Catatan Pengiriman')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->adminTransition(
                            $record,
                            'shipped',
                            Auth::user(),
                            $data['notes'] ?? null,
                            $data['tracking_number'],
                        );
                        Notification::make()->title('Order dikirim, resi: '.$data['tracking_number'])->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('deliver')
                ->label('Tandai Terkirim')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $record->status === 'shipped')
                ->requiresConfirmation()
                ->modalHeading('Tandai Order Terkirim?')
                ->action(function () use ($service, $record) {
                    try {
                        $service->adminTransition($record, 'delivered', Auth::user());
                        Notification::make()->title('Order ditandai terkirim')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('complete')
                ->label('Selesaikan')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $record->status === 'delivered')
                ->requiresConfirmation()
                ->action(function () use ($service, $record) {
                    try {
                        $service->adminTransition($record, 'completed', Auth::user());
                        Notification::make()->title('Order selesai')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('cancel')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($record->status, ['pending_payment', 'payment_pending', 'paid', 'processing', 'ready_to_ship'], true))
                ->form([
                    Textarea::make('notes')
                        ->label('Alasan Pembatalan')
                        ->required()
                        ->minLength(5)
                        ->rows(2),
                ])
                ->requiresConfirmation()
                ->modalHeading('Batalkan Order?')
                ->modalDescription('Stok akan dikembalikan ke inventory. Tindakan ini tidak dapat dibatalkan.')
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->adminTransition($record, 'cancelled', Auth::user(), $data['notes']);
                        Notification::make()->title('Order dibatalkan')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}