@php
    /** @var \App\Models\Quotation $quotation */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-success" style="font-size: 13px; padding: 6px 16px;">
            ✓ Quotation Diterima
        </span>
    </div>

    <p class="email-greeting">Halo Tim KARTEKS,</p>
    <p class="email-message">
        Customer menerima quotation di bawah ini. Tim perlu follow-up untuk proses selanjutnya.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Quotation',
        'rows' => [
            ['No. Quotation', '<strong style="font-family: monospace;">' . $quotation->quotation_number . '</strong>'],
            ['Customer', $quotation->customer?->name],
            ['Judul', $quotation->title],
            ['Total', '<strong style="color: #047857;">' . $symbol . ' ' . number_format((float) $quotation->total, 0, ',', '.') . '</strong>'],
            ['Diterima', $quotation->accepted_at?->format('d F Y, H:i') . ' WIB'],
        ],
    ])

    @if($quotation->notes)
        <div style="background-color: #f9fafb; border-left: 4px solid #10b981; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #047857;">Catatan Customer</p>
            <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.6; white-space: pre-line;">{{ $quotation->notes }}</p>
        </div>
    @endif

    @include('emails.partials.button', [
        'url' => url('/admin/quotations/' . $quotation->id),
        'label' => 'Buka di Admin Panel',
    ])
@endsection
