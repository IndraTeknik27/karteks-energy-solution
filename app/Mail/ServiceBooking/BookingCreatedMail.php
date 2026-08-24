<?php

namespace App\Mail\ServiceBooking;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingCreatedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\ServiceBooking $booking)
    {
        parent::__construct();
        $this->headerTitle = 'Booking Baru';
        $this->headerSubtitle = $booking->booking_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Service Booking Baru: {$this->booking->booking_number}",
            ['service-booking', 'created', $this->booking->booking_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.created',
            with: ['booking' => $this->booking->load('service')],
        );
    }
}