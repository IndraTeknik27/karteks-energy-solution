<?php

namespace App\Mail\Quotation;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuotationRejectedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\Quotation $quotation)
    {
        parent::__construct();
        $this->headerTitle = 'Quotation Ditolak';
        $this->headerSubtitle = $quotation->quotation_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Quotation Ditolak: {$this->quotation->quotation_number}",
            ['quotation', 'rejected', $this->quotation->quotation_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotations.rejected',
            with: ['quotation' => $this->quotation->load('customer')],
        );
    }
}