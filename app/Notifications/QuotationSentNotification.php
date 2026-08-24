<?php

namespace App\Notifications;

use App\Mail\Quotation\QuotationSentMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class QuotationSentNotification extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\Quotation $quotation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): QuotationSentMail
    {
        return new QuotationSentMail($this->quotation);
    }

    public function toDatabase(object $notifiable): array
    {
        $symbol = config('karteks.locale.currency_symbol', 'Rp');
        return [
            'type' => 'quotation_sent',
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->quotation_number,
            'title' => 'Quotation Baru',
            'message' => "Quotation #{$this->quotation->quotation_number} - Total {$symbol} " . number_format($this->quotation->total, 0, ',', '.'),
            'action_url' => "/dashboard/quotation/{$this->quotation->id}",
            'icon' => '📋',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}