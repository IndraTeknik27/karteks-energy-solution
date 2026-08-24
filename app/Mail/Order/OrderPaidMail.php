<?php

namespace App\Mail\Order;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderPaidMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\Order $order)
    {
        parent::__construct();
        $this->headerTitle = 'Pembayaran Berhasil';
        $this->headerSubtitle = $order->order_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "Pembayaran Diterima - Pesanan #{$this->order->order_number}",
            ['order', 'paid', $this->order->order_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.paid',
            with: ['order' => $this->order->load('items', 'shipment')],
        );
    }
}