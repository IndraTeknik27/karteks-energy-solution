<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $bookingNumber,
        public string $serviceName,
        public ?\Carbon\Carbon $scheduledAt = null,
        public ?string $technicianName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Booking Dikonfirmasi - {$this->bookingNumber}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Booking service Anda telah dikonfirmasi oleh tim KARTEKS.")
            ->line("Nomor Booking: {$this->bookingNumber}")
            ->line("Layanan: {$this->serviceName}");

        if ($this->scheduledAt) {
            $mail->line("Jadwal: {$this->scheduledAt->format('l, d F Y H:i')}");
        }

        if ($this->technicianName) {
            $mail->line("Teknisi: {$this->technicianName}");
        }

        return $mail
            ->action('Lihat Detail', url('/dashboard/bookings/'.$this->bookingNumber))
            ->line('Kami akan menghubungi Anda jika ada perubahan jadwal.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'service_booking_confirmed',
            'booking_number' => $this->bookingNumber,
            'service_name' => $this->serviceName,
            'scheduled_at' => $this->scheduledAt?->toIso8601String(),
            'technician_name' => $this->technicianName,
        ];
    }
}