<?php

namespace App\Mail\Contact;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminNotificationMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\ContactMessage $message)
    {
        parent::__construct();
        $this->headerTitle = 'Pesan Kontak Baru';
        $this->headerSubtitle = 'Tunggu balasan dari customer';
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Pesan Baru: {$this->message->subject}",
            ['contact', 'admin-notification'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.admin-notification',
            with: ['contactMessage' => $this->message],
        );
    }
}