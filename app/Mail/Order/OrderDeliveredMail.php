<?php

namespace App\Mail\Order;

use App\Mail\AbstractTransactionalMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderDeliveredMail extends AbstractTransactionalMail
{
    public function __construct(public \App\Models\Order $order)
    {
        parent::__construct();
        $this->headerTitle = 'Pesanan Diterima';
        $this->headerSubtitle = $order->order_number;
    }

    public function envelope(): Envelope
    {
        return $this->buildEnvelope(
            "Pesanan #{$this->order->order_number} Telah Diterima",
            ['order', 'delivered', $this->order->order_number],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.delivered',
            with: ['order' => $this->order->load('items')],
        );
    }
}