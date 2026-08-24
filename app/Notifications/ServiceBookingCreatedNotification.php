<?php

namespace App\Notifications;

use App\Mail\ServiceBooking\BookingCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\ServiceBooking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): BookingCreatedMail
    {
        return new BookingCreatedMail($this->booking);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'service_booking_created',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'title' => 'Service Booking Baru',
            'message' => "Booking #{$this->booking->booking_number} dari {$this->booking->customer_name} untuk " . ($this->booking->service?->name ?? 'layanan'),
            'action_url' => "/admin/service-bookings/{$this->booking->id}",
            'icon' => '📅',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}