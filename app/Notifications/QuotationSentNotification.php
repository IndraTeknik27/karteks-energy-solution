<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $quotationNumber,
        public string $title,
        public float $total,
        public ?\Carbon\Carbon $validUntil = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Quotation Baru dari KARTEKS - {$this->quotationNumber}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Quotation baru telah disiapkan untuk Anda.")
            ->line("Nomor Quotation: {$this->quotationNumber}")
            ->line("Judul: {$this->title}")
            ->line("Total: Rp ".number_format($this->total, 0, ',', '.'));

        if ($this->validUntil) {
            $mail->line("Berlaku sampai: {$this->validUntil->format('d F Y')}");
        }

        return $mail
            ->action('Lihat Quotation', url('/dashboard/quotations/'.$this->quotationNumber))
            ->line('Silakan review dan konfirmasi paling lambat sebelum tanggal berlaku.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_sent',
            'quotation_number' => $this->quotationNumber,
            'title' => $this->title,
            'total' => $this->total,
            'valid_until' => $this->validUntil?->toIso8601String(),
        ];
    }
}