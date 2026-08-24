@php
    /** @var \App\Models\Order $order */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-success" style="font-size: 13px; padding: 6px 16px;">
            ✓ Pesanan Diterima
        </span>
    </div>

    <p class="email-greeting">Halo {{ $order->customer_name ?? 'Customer' }},</p>
    <p class="email-message">
        Pesanan Anda telah sampai di tujuan! Mohon konfirmasi penerimaan agar kami bisa menyelesaikan transaksi.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Pengiriman',
        'rows' => [
            ['No. Pesanan', $order->order_number],
            ['Tanggal Diterima', $order->delivered_at?->format('d F Y, H:i') . ' WIB' ?? now()->format('d F Y, H:i') . ' WIB'],
            ['Status', '<span class="email-badge email-badge-success">' . str_replace('_', ' ', ucfirst($order->status)) . '</span>'],
        ],
    ])

    @if($order->items && $order->items->count() > 0)
        <p class="email-message" style="margin-top: 24px;"><strong>Item yang diterima:</strong></p>
        @include('emails.partials.order-items-table', [
            'items' => $order->items,
            'currency' => $symbol,
        ])
    @endif

    <p class="email-message" style="margin-top: 24px;">
        Jika pesanan sudah sesuai, mohon konfirmasi penerimaan. Jika ada masalah, silakan hubungi customer service kami.
    </p>

    @include('emails.partials.button', [
        'url' => url('/dashboard/orders/' . $order->order_number . '/confirm-delivery'),
        'label' => 'Konfirmasi Penerimaan',
    ])

    @include('emails.partials.button', [
        'url' => url('/dashboard/orders/' . $order->order_number),
        'label' => 'Lihat Pesanan',
        'secondary' => true,
    ])
@endsection
