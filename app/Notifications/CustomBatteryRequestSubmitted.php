<?php

namespace App\Notifications;

use App\Mail\CustomBattery\RequestSubmittedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class CustomBatteryRequestSubmitted extends Notification
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

    public function toMail(object $notifiable): RequestSubmittedMail
    {
        return (new RequestSubmittedMail($this->request))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'custom_battery_submitted',
            'request_id' => $this->request->id,
            'request_number' => $this->request->request_number,
            'title' => 'Custom Battery Request Baru',
            'message' => "Request #{$this->request->request_number} dari {$this->request->customer?->name} menunggu review.",
            'action_url' => "/admin/custom-battery-requests/{$this->request->id}",
            'icon' => '🔋',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}