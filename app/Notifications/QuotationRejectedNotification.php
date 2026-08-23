<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $quotationNumber,
        public string $customerName,
        public string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Quotation Ditolak - {$this->quotationNumber}")
            ->greeting("Halo Tim KARTEKS,")
            ->line("Customer {$this->customerName} menolak quotation.")
            ->line("Nomor Quotation: {$this->quotationNumber}")
            ->line("Alasan penolakan: {$this->reason}")
            ->action('Lihat Detail', url('/admin/quotations/'.$this->quotationNumber));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_rejected',
            'quotation_number' => $this->quotationNumber,
            'customer_name' => $this->customerName,
            'reason' => $this->reason,
        ];
    }
}