<?php

namespace App\Filament\Admin\Resources\CustomBatteryRequests\Pages;

use App\Filament\Admin\Resources\CustomBatteryRequests\CustomBatteryRequestResource;
use App\Models\CustomBatteryRequest;
use App\Services\V1\CustomBatteryRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewCustomBatteryRequest extends ViewRecord
{
    protected static string $resource = CustomBatteryRequestResource::class;

    protected function getHeaderActions(): array
    {
        $service = app(CustomBatteryRequestService::class);
        $record = $this->getRecord();

        return [
            Action::make('transition')
                ->label('Ubah Status')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->visible(fn () => ! in_array($record->status, ['completed', 'cancelled', 'rejected'], true))
                ->form([
                    Select::make('new_status')
                        ->label('Status Baru')
                        ->options(fn () => $this->getAvailableTransitions($record->status))
                        ->required(),
                    TextInput::make('estimated_price')
                        ->label('Harga Estimasi (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->visible(fn ($get) => $get('new_status') === 'quoted'),
                    TextInput::make('final_price')
                        ->label('Harga Final (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->visible(fn ($get) => $get('new_status') === 'completed'),
                    Textarea::make('admin_notes')
                        ->label('Catatan Admin')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->transitionStatus(
                            $record,
                            $data['new_status'],
                            Auth::user(),
                            $data,
                        );
                        Notification::make()->title('Status berhasil diubah')->success()->send();
                        $this->refreshFormData();
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('requestRevision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('warning')
                ->visible(fn () => in_array($record->status, ['under_review', 'revision_requested'], true))
                ->form([
                    Textarea::make('admin_note')
                        ->label('Catatan Revisi')
                        ->required()
                        ->rows(4)
                        ->placeholder('Jelaskan apa yang perlu direvisi oleh customer...'),
                ])
                ->action(function (array $data) use ($service, $record) {
                    try {
                        $service->requestRevision($record, Auth::user(), $data['admin_note']);
                        Notification::make()->title('Revisi diminta')->success()->send();
                        $this->refreshFormData();
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('uploadFile')
                ->label('Upload File')
                ->icon('heroicon-o-paper-clip')
                ->color('gray')
                ->form([
                    FileUpload::make('file')
                        ->label('File')
                        ->required()
                        ->disk('public')
                        ->directory("custom-battery/{$record->request_number}"),
                ])
                ->action(function (array $data) use ($service, $record) {
                    $file = $data['file'];
                    $service->uploadFile($record, \Illuminate\Http\UploadedFile::createFromBase($file), 'admin');
                    Notification::make()->title('File berhasil diunggah')->success()->send();
                    $this->refreshFormData();
                }),
        ];
    }

    protected function getAvailableTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'submitted' => ['under_review' => 'Under Review', 'cancelled' => 'Cancelled'],
            'under_review' => [
                'quoted' => 'Quoted',
                'rejected' => 'Rejected',
                'cancelled' => 'Cancelled',
            ],
            'revision_requested' => ['under_review' => 'Under Review', 'cancelled' => 'Cancelled'],
            'quoted' => ['approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'],
            'approved' => ['in_production' => 'In Production', 'cancelled' => 'Cancelled'],
            'in_production' => ['completed' => 'Completed'],
            default => [],
        };
    }
}