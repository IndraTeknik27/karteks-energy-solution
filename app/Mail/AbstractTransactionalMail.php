<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base class untuk semua transactional email KARTEKS.
 *
 * Pattern:
 * - Subject auto-set di child constructor
 * - Header title/subtitle passed via Blade props
 * - Footer auto-include unsubscribe link (jika ada $unsubscribeUrl)
 */
abstract class AbstractTransactionalMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $headerTitle = null;

    public ?string $headerSubtitle = null;

    public ?string $unsubscribeUrl = null;

    public function __construct()
    {
        // Set default from address dari config.karteks
        $this->from(
            config('karteks.email.from_address', 'karteksenergy27@gmail.com'),
            config('karteks.email.from_name', 'KARTEKS ENERGY SOLUTION'),
        );
    }

    /**
     * Build envelope dengan subject + metadata.
     */
    abstract public function envelope(): Envelope;

    /**
     * Build content (Blade view path).
     */
    abstract public function content(): Content;

    /**
     * Build envelope dinamis (helper).
     */
    protected function buildEnvelope(string $subject, array $tags = []): Envelope
    {
        return new Envelope(
            subject: $subject,
            tags: $tags,
        );
    }
}