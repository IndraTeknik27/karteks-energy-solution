<?php

namespace App\Mail\Quotation;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuotationAcceptedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\Quotation $quotation)
    {
        parent::__construct();
        $this->headerTitle = 'Quotation Diterima';
        $this->headerSubtitle = $quotation->quotation_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Quotation Diterima: {$this->quotation->quotation_number}",
            ['quotation', 'accepted', $this->quotation->quotation_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotations.accepted',
            with: ['quotation' => $this->quotation->load('customer')],
        );
    }
}