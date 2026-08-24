<?php

namespace App\Mail\Quotation;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuotationSentMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\Quotation $quotation)
    {
        parent::__construct();
        $this->headerTitle = 'Quotation Baru';
        $this->headerSubtitle = $quotation->quotation_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "Quotation Baru dari KARTEKS - {$this->quotation->quotation_number}",
            ['quotation', 'sent', $this->quotation->quotation_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotations.sent',
            with: ['quotation' => $this->quotation->load(['customer', 'items'])],
        );
    }
}