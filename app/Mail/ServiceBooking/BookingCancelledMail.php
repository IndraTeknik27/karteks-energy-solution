<?php

namespace App\Mail\ServiceBooking;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingCancelledMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\ServiceBooking $booking, public string $recipient = 'customer')
    {
        parent::__construct();
        $this->headerTitle = 'Booking Dibatalkan';
        $this->headerSubtitle = $booking->booking_number;
    }

    public function envelope(): Envelope
    {
        $prefix = $this->recipient === 'admin' ? '[KARTEKS] ' : '';
        return $this->buildEnvelope(
            "{$prefix}Booking Dibatalkan: {$this->booking->booking_number}",
            ['service-booking', 'cancelled', $this->booking->booking_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.cancelled',
            with: [
                'booking' => $this->booking->load('service'),
                'recipient' => $this->recipient,
            ],
        );
    }
}