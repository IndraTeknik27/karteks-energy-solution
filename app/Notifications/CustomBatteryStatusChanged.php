<?php

namespace App\Notifications;

use App\Mail\CustomBattery\StatusChangedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CustomBatteryStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\CustomBatteryRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (! empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): StatusChangedMail
    {
        return (new StatusChangedMail($this->request))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'custom_battery_status_changed',
            'request_id' => $this->request->id,
            'request_number' => $this->request->request_number,
            'title' => 'Update Status Custom Battery',
            'message' => "Request #{$this->request->request_number} sekarang berstatus: " . str_replace('_', ' ', ucfirst($this->request->status)),
            'action_url' => "/dashboard/custom-battery/{$this->request->id}",
            'icon' => '🔋',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}