<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $bookingNumber,
        public string $customerName,
        public string $serviceName,
        public ?\Carbon\Carbon $scheduledAt = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Booking Service Baru - {$this->bookingNumber}")
            ->greeting("Halo Tim KARTEKS,")
            ->line("Customer {$this->customerName} baru saja membuat booking service.")
            ->line("Nomor Booking: {$this->bookingNumber}")
            ->line("Layanan: {$this->serviceName}")
            ->line("Jadwal: ".($this->scheduledAt?->format('d F Y, H:i') ?? 'Belum ditentukan'))
            ->action('Tinjau di Admin Panel', url('/admin/service-bookings/'.$this->bookingNumber))
            ->line('Mohon konfirmasi secepatnya.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'service_booking_created',
            'booking_number' => $this->bookingNumber,
            'customer_name' => $this->customerName,
            'service_name' => $this->serviceName,
            'scheduled_at' => $this->scheduledAt?->toIso8601String(),
        ];
    }
}