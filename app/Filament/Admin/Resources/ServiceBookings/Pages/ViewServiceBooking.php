<?php

namespace App\Filament\Admin\Resources\ServiceBookings\Pages;

use App\Filament\Admin\Resources\ServiceBookings\ServiceBookingResource;
use App\Services\V1\ServiceBookingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewServiceBooking extends ViewRecord
{
    protected static string $resource = ServiceBookingResource::class;

    protected function getHeaderActions(): array
    {
        $service = app(ServiceBookingService::class);
        $record = $this->getRecord();

        return [
            Action::make('confirm')
                ->label('Konfirmasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $record->status === 'pending')
                ->form([
                    Select::make('technician_id')
                        ->label('Assign Teknisi')
                        ->relationship('technician', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Opsional. Bisa di-assign nanti.'),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->confirm($record, Auth::user(), $data['technician_id'] ?? null);
                        Notification::make()->title('Booking dikonfirmasi')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('assignTechnician')
                ->label('Assign Teknisi')
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->visible(fn () => in_array($record->status, ['pending', 'confirmed'], true))
                ->form([
                    Select::make('technician_id')
                        ->label('Pilih Teknisi')
                        ->relationship('technician', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->assignTechnician($record, Auth::user(), $data['technician_id']);
                        Notification::make()->title('Teknisi berhasil di-assign')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('reschedule')
                ->label('Reschedule')
                ->icon('heroicon-o-calendar')
                ->color('warning')
                ->visible(fn () => $record->is_cancellable)
                ->form([
                    DateTimePicker::make('scheduled_at')
                        ->label('Jadwal Baru')
                        ->required()
                        ->minDate(now())
                        ->native(false),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->reschedule($record, Auth::user(), \Carbon\Carbon::parse($data['scheduled_at']), $data['notes'] ?? null);
                        Notification::make()->title('Booking dijadwal ulang')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('start')
                ->label('Mulai Layanan')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => $record->status === 'confirmed')
                ->requiresConfirmation()
                ->action(function () use ($service, $record) {
                    try {
                        $service->start($record, Auth::user());
                        Notification::make()->title('Layanan dimulai')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('complete')
                ->label('Selesaikan')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $record->status === 'in_progress')
                ->form([
                    TextInput::make('final_cost')
                        ->label('Biaya Final (Rp)')
                        ->numeric()
                        ->prefix('Rp'),
                    Textarea::make('admin_notes')
                        ->label('Catatan Penyelesaian')
                        ->rows(2),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->complete(
                            $record,
                            Auth::user(),
                            isset($data['final_cost']) ? (float) $data['final_cost'] : null,
                            $data['admin_notes'] ?? null,
                        );
                        Notification::make()->title('Layanan selesai')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('cancel')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $record->is_cancellable)
                ->form([
                    Textarea::make('reason')
                        ->label('Alasan Pembatalan')
                        ->required()
                        ->minLength(5)
                        ->rows(2),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->cancelByAdmin($record, Auth::user(), $data['reason']);
                        Notification::make()->title('Booking dibatalkan')->success()->send();
                        $this->refreshFormData();
                    } catch (\Throwable $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}