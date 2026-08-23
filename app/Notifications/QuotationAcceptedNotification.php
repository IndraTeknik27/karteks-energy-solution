<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $quotationNumber,
        public string $customerName,
        public float $total,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Quotation Diterima - {$this->quotationNumber}")
            ->greeting("Halo Tim KARTEKS,")
            ->line("Customer {$this->customerName} telah menerima quotation.")
            ->line("Nomor Quotation: {$this->quotationNumber}")
            ->line("Total: Rp ".number_format($this->total, 0, ',', '.'))
            ->line('Mohon lanjutkan ke tahap produksi/pengiriman sesuai agreement.')
            ->action('Lihat Detail', url('/admin/quotations/'.$this->quotationNumber));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_accepted',
            'quotation_number' => $this->quotationNumber,
            'customer_name' => $this->customerName,
            'total' => $this->total,
        ];
    }
}