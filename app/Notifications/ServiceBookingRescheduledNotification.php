<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $bookingNumber,
        public string $serviceName,
        public ?\Carbon\Carbon $oldScheduledAt = null,
        public ?\Carbon\Carbon $newScheduledAt = null,
        public ?string $changedByName = null,
        public string $recipientRole = 'customer',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Jadwal Booking Diubah - {$this->bookingNumber}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Jadwal booking service Anda telah diperbarui.")
            ->line("Nomor Booking: {$this->bookingNumber}")
            ->line("Layanan: {$this->serviceName}");

        if ($this->oldScheduledAt) {
            $mail->line("Jadwal Lama: {$this->oldScheduledAt->format('d F Y, H:i')}");
        }
        if ($this->newScheduledAt) {
            $mail->line("Jadwal Baru: {$this->newScheduledAt->format('d F Y, H:i')}");
        }

        $actionUrl = $this->recipientRole === 'customer'
            ? url('/dashboard/bookings/'.$this->bookingNumber)
            : url('/admin/service-bookings/'.$this->bookingNumber);

        return $mail
            ->action('Lihat Detail', $actionUrl)
            ->line('Mohon konfirmasi ulang jika jadwal baru tidak memungkinkan.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'service_booking_rescheduled',
            'booking_number' => $this->bookingNumber,
            'service_name' => $this->serviceName,
            'old_scheduled_at' => $this->oldScheduledAt?->toIso8601String(),
            'new_scheduled_at' => $this->newScheduledAt?->toIso8601String(),
            'changed_by_name' => $this->changedByName,
        ];
    }
}