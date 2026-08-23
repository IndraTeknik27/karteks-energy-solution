<?php

namespace App\Filament\Admin\Resources\Reviews\Pages;

use App\Filament\Admin\Resources\Reviews\ReviewResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            Action::make('toggleApproval')
                ->label(fn () => $record->is_approved ? 'Unapprove' : 'Approve')
                ->icon(fn () => $record->is_approved ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $record->is_approved ? 'warning' : 'success')
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $newState = ! $record->is_approved;
                    $record->update([
                        'is_approved' => $newState,
                        'approved_at' => $newState ? now() : null,
                    ]);
                    Notification::make()
                        ->title($newState ? 'Review di-approve' : 'Review di-unapprove')
                        ->success()
                        ->send();
                    $this->refreshFormData();
                }),
        ];
    }
}