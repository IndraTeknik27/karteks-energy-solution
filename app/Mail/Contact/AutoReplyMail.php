<?php

namespace App\Mail\Contact;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AutoReplyMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\ContactMessage $message)
    {
        parent::__construct();
        $this->headerTitle = 'Pesan Diterima';
        $this->headerSubtitle = 'Kami akan membalas segera';
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "Terima kasih telah menghubungi KARTEKS - #{$this->message->id}",
            ['contact', 'auto-reply'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.auto-reply',
            with: ['contactMessage' => $this->message],
        );
    }
}