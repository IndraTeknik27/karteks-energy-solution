<?php

namespace App\Notifications;

use App\Mail\Quotation\QuotationRejectedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class QuotationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\Quotation $quotation)
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

    public function toMail(object $notifiable): QuotationRejectedMail
    {
        return (new QuotationRejectedMail($this->quotation))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'quotation_rejected',
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->quotation_number,
            'title' => 'Quotation Ditolak',
            'message' => "Customer menolak Quotation #{$this->quotation->quotation_number}.",
            'action_url' => "/admin/quotations/{$this->quotation->id}",
            'icon' => '✗',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}