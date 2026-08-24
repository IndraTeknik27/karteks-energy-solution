<?php

namespace App\Mail\CustomBattery;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RequestSubmittedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\CustomBatteryRequest $request)
    {
        parent::__construct();
        $this->headerTitle = 'Custom Battery Request';
        $this->headerSubtitle = $request->request_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Custom Battery Request Baru: {$this->request->request_number}",
            ['custom-battery', 'submitted', $this->request->request_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-battery.submitted',
            with: ['request' => $this->request->load('customer')],
        );
    }
}