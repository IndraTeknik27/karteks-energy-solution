@php
    /** @var \App\Models\Order $order */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
    $shipment = $order->shipment ?? null;
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-info" style="font-size: 13px; padding: 6px 16px;">
            📦 Pesanan Dikirim
        </span>
    </div>

    <p class="email-greeting">Halo {{ $order->customer_name ?? 'Customer' }},</p>
    <p class="email-message">
        Pesanan Anda telah dikirim dan sedang dalam perjalanan. Berikut detail tracking untuk Anda:
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Pengiriman',
        'rows' => [
            ['No. Pesanan', $order->order_number],
            ['No. Resi', '<strong style="font-family: monospace; color: #047857;">' . ($shipment?->tracking_number ?? '-') . '</strong>'],
            ['Kurir', strtoupper($shipment?->courier_code ?? '-') . ' ' . ($shipment?->courier_name ?? '')],
            ['Layanan', strtoupper($shipment?->service_type ?? '-')],
            ['Estimasi Tiba', $shipment?->estimated_delivery?->format('d F Y') ?? '1-3 hari kerja'],
        ],
    ])

    @if($shipment && $shipment->trackingHistories && $shipment->trackingHistories->count() > 0)
        <p class="email-message" style="margin-top: 24px;"><strong>Status Tracking:</strong></p>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            @foreach($shipment->trackingHistories->take(5) as $history)
                <tr>
                    <td style="padding: 8px 0; font-size: 13px; color: #10b981; vertical-align: top; width: 130px;">
                        <strong>{{ $history->occurred_at?->format('d M H:i') }}</strong>
                    </td>
                    <td style="padding: 8px 0; font-size: 13px; color: #374151; vertical-align: top;">
                        {{ $history->status }}
                        @if($history->location)
                            <br><span style="color: #9ca3af; font-size: 12px;">{{ $history->location }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <p class="email-message" style="margin-top: 24px;">
        Anda dapat melacak pesanan secara real-time melalui link di bawah ini.
    </p>

    @include('emails.partials.button', [
        'url' => url('/dashboard/orders/' . $order->order_number . '/tracking'),
        'label' => 'Lacak Pesanan',
    ])
@endsection
