<?php

namespace App\Notifications;

use App\Mail\Quotation\QuotationAcceptedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class QuotationAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\Quotation $quotation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): QuotationAcceptedMail
    {
        return new QuotationAcceptedMail($this->quotation);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'quotation_accepted',
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->quotation_number,
            'title' => 'Quotation Diterima',
            'message' => "Customer menerima Quotation #{$this->quotation->quotation_number}.",
            'action_url' => "/admin/quotations/{$this->quotation->id}",
            'icon' => '✓',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}