<?php

namespace App\Mail\CustomBattery;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RevisionRequestedMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\CustomBatteryRevision $revision)
    {
        parent::__construct();
        $this->headerTitle = 'Revisi Diminta';
        $this->headerSubtitle = $revision->request?->request_number ?? 'Revisi';
    }

    public function envelope(): Envelope
    {
        $number = $this->revision->request?->request_number ?? 'Revisi';
        return $this->buildEnvelope(
            "[KARTEKS] Revisi Diminta untuk Request {$number}",
            ['custom-battery', 'revision-requested', $number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-battery.revision-requested',
            with: ['revision' => $this->revision->load('request.customer')],
        );
    }
}