<?php

namespace App\Mail\CustomBattery;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StatusChangedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\CustomBatteryRequest $request)
    {
        parent::__construct();
        $this->headerTitle = 'Update Status Request';
        $this->headerSubtitle = $request->request_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "[KARTEKS] Update Status: {$this->request->request_number} - " . str_replace('_', ' ', ucfirst($this->request->status)),
            ['custom-battery', 'status-changed', $this->request->request_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-battery.status-changed',
            with: ['request' => $this->request->load('customer')],
        );
    }
}