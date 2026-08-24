<?php

namespace App\Mail\ServiceBooking;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingConfirmedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\ServiceBooking $booking)
    {
        parent::__construct();
        $this->headerTitle = 'Booking Dikonfirmasi';
        $this->headerSubtitle = $booking->booking_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Service Booking Dikonfirmasi: {$this->booking->booking_number}",
            ['service-booking', 'confirmed', $this->booking->booking_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.confirmed',
            with: ['booking' => $this->booking->load(['service', 'technician'])],
        );
    }
}