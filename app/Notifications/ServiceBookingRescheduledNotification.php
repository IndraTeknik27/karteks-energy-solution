<?php

namespace App\Notifications;

use App\Mail\ServiceBooking\BookingRescheduledMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ServiceBookingRescheduledNotification extends Notification
{
    use Queueable;

    public function __construct(public \App\Models\ServiceBooking $booking)
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

    public function toMail(object $notifiable): BookingRescheduledMail
    {
        return (new BookingRescheduledMail($this->booking))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'service_booking_rescheduled',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'title' => 'Jadwal Service Berubah',
            'message' => "Booking #{$this->booking->booking_number} dijadwalkan ulang ke " . $this->booking->scheduled_at->format('d F Y H:i'),
            'action_url' => "/dashboard/booking/{$this->booking->id}",
            'icon' => '⏰',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}