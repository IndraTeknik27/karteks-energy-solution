<?php

namespace App\Notifications;

use App\Mail\ServiceBooking\BookingConfirmedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\ServiceBooking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): BookingConfirmedMail
    {
        return new BookingConfirmedMail($this->booking);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'service_booking_confirmed',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'title' => 'Booking Dikonfirmasi',
            'message' => "Booking #{$this->booking->booking_number} telah dikonfirmasi untuk " . $this->booking->scheduled_at->format('d F Y H:i'),
            'action_url' => "/dashboard/booking/{$this->booking->id}",
            'icon' => '✓',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}