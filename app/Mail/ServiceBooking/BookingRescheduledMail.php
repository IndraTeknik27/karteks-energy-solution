<?php

namespace App\Mail\ServiceBooking;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingRescheduledMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\ServiceBooking $booking)
    {
        parent::__construct();
        $this->headerTitle = 'Jadwal Berubah';
        $this->headerSubtitle = $booking->booking_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Jadwal Service Berubah: {$this->booking->booking_number}",
            ['service-booking', 'rescheduled', $this->booking->booking_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bookings.rescheduled',
            with: ['booking' => $this->booking->load('service')],
        );
    }
}