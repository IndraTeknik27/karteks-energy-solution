<?php

namespace App\Notifications;

use App\Mail\ServiceBooking\BookingCancelledMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\ServiceBooking $booking, public string $recipient = 'customer')
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (! empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): BookingCancelledMail
    {
        return (new BookingCancelledMail($this->booking, $this->recipient))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        $message = $this->recipient === 'admin'
            ? "Customer membatalkan Booking #{$this->booking->booking_number}"
            : "Booking #{$this->booking->booking_number} telah dibatalkan.";

        return [
            'type' => 'service_booking_cancelled',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'title' => 'Booking Dibatalkan',
            'message' => $message,
            'action_url' => $this->recipient === 'admin'
                ? "/admin/service-bookings/{$this->booking->id}"
                : "/dashboard/booking/{$this->booking->id}",
            'icon' => '✗',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}