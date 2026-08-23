<?php

namespace App\Filament\Admin\Resources\Shipments\Pages;

use App\Filament\Admin\Resources\Shipments\ShipmentResource;
use App\Models\ShipmentTracking;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            Action::make('generateResi')
                ->label('Generate Resi')
                ->icon('heroicon-o-qr-code')
                ->color('info')
                ->visible(fn () => empty($record->tracking_number))
                ->form([
                    TextInput::make('courier_code')
                        ->label('Kurir')
                        ->options([
                            'jne' => 'JNE',
                            'pos' => 'POS',
                            'tiki' => 'TIKI',
                            'sicepat' => 'SiCepat',
                            'jnt' => 'J&T',
                        ])
                        ->required(),
                    TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->default(fn () => strtoupper(substr($record->courier_code ?? 'TRK', 0, 3)).Str::random(10))
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'courier_code' => $data['courier_code'],
                        'tracking_number' => $data['tracking_number'],
                        'status' => 'packed',
                    ]);

                    ShipmentTracking::create([
                        'shipment_id' => $record->id,
                        'status' => 'Picked up by courier',
                        'description' => "Resi {$data['tracking_number']} telah di-generate.",
                        'location' => 'KARTEKS Warehouse',
                        'occurred_at' => now(),
                    ]);

                    Notification::make()->title('Resi di-generate: '.$data['tracking_number'])->success()->send();
                    $this->refreshFormData();
                }),

            Action::make('markShipped')
                ->label('Tandai Dikirim')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->visible(fn () => in_array($record->status, ['packed', 'pending'], true))
                ->form([
                    DateTimePicker::make('shipped_at')
                        ->label('Waktu Kirim')
                        ->default(now())
                        ->required()
                        ->native(false),
                    Textarea::make('description')
                        ->label('Catatan Pengiriman')
                        ->rows(2)
                        ->default('Paket telah diserahkan ke kurir'),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'status' => 'shipped',
                        'shipped_at' => $data['shipped_at'],
                    ]);

                    ShipmentTracking::create([
                        'shipment_id' => $record->id,
                        'status' => 'In transit',
                        'description' => $data['description'] ?? 'Paket dalam perjalanan.',
                        'occurred_at' => $data['shipped_at'],
                    ]);

                    Notification::make()->title('Shipment ditandai dikirim')->success()->send();
                    $this->refreshFormData();
                }),

            Action::make('markDelivered')
                ->label('Tandai Terkirim')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($record->status, ['shipped', 'in_transit'], true))
                ->form([
                    DateTimePicker::make('delivered_at')
                        ->label('Waktu Terkirim')
                        ->default(now())
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'status' => 'delivered',
                        'delivered_at' => $data['delivered_at'],
                    ]);

                    ShipmentTracking::create([
                        'shipment_id' => $record->id,
                        'status' => 'Delivered',
                        'description' => 'Paket telah diterima oleh penerima.',
                        'occurred_at' => $data['delivered_at'],
                    ]);

                    if ($record->order) {
                        $record->order->update([
                            'status' => 'delivered',
                            'delivered_at' => $data['delivered_at'],
                        ]);
                    }

                    Notification::make()->title('Shipment terkirim')->success()->send();
                    $this->refreshFormData();
                }),

            Action::make('addTrackingUpdate')
                ->label('Tambah Tracking')
                ->icon('heroicon-o-map-pin')
                ->color('gray')
                ->visible(fn () => ! empty($record->tracking_number))
                ->form([
                    TextInput::make('status')->label('Status')->required(),
                    TextInput::make('location')->label('Lokasi'),
                    DateTimePicker::make('occurred_at')->label('Waktu')->default(now())->required()->native(false),
                    Textarea::make('description')->rows(2),
                ])
                ->action(function (array $data) use ($record) {
                    ShipmentTracking::create([
                        'shipment_id' => $record->id,
                        'status' => $data['status'],
                        'location' => $data['location'] ?? null,
                        'description' => $data['description'] ?? null,
                        'occurred_at' => $data['occurred_at'],
                    ]);
                    Notification::make()->title('Tracking update ditambahkan')->success()->send();
                    $this->refreshFormData();
                }),
        ];
    }
}