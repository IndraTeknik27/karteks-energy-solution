<?php

namespace App\Notifications;

use App\Mail\CustomBattery\RevisionRequestedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CustomBatteryRevisionRequested extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\CustomBatteryRevision $revision)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): RevisionRequestedMail
    {
        return new RevisionRequestedMail($this->revision);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'custom_battery_revision_requested',
            'request_id' => $this->revision->request_id,
            'request_number' => $this->revision->request?->request_number,
            'title' => 'Revisi Diminta untuk Custom Battery',
            'message' => "Request #{$this->revision->request?->request_number} butuh revisi. Cek catatan admin.",
            'action_url' => "/dashboard/custom-battery/{$this->revision->request_id}",
            'icon' => '📝',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}