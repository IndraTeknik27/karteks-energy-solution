<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $bookingNumber,
        public string $serviceName,
        public ?\Carbon\Carbon $scheduledAt = null,
        public string $reason = '',
        public string $cancelledBy = 'system',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Booking Dibatalkan - {$this->bookingNumber}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Booking service telah dibatalkan.")
            ->line("Nomor Booking: {$this->bookingNumber}")
            ->line("Layanan: {$this->serviceName}");

        if ($this->scheduledAt) {
            $mail->line("Jadwal Awal: {$this->scheduledAt->format('d F Y, H:i')}");
        }

        $mail->line("Dibatalkan oleh: ".ucfirst($this->cancelledBy));

        if ($this->reason) {
            $mail->line("Alasan: {$this->reason}");
        }

        $actionUrl = $this->cancelledBy === 'customer'
            ? url('/dashboard/bookings')
            : url('/admin/service-bookings/'.$this->bookingNumber);

        return $mail->action('Lihat Detail', $actionUrl);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'service_booking_cancelled',
            'booking_number' => $this->bookingNumber,
            'service_name' => $this->serviceName,
            'scheduled_at' => $this->scheduledAt?->toIso8601String(),
            'reason' => $this->reason,
            'cancelled_by' => $this->cancelledBy,
        ];
    }
}