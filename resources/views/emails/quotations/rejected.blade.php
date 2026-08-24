@php
    /** @var \App\Models\Quotation $quotation */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-danger" style="font-size: 13px; padding: 6px 16px;">
            ✗ Quotation Ditolak
        </span>
    </div>

    <p class="email-greeting">Halo Tim KARTEKS,</p>
    <p class="email-message">
        Customer menolak quotation di bawah ini. Mohon ditinjau untuk perbaikan atau follow-up alternatif.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Quotation',
        'rows' => [
            ['No. Quotation', '<strong style="font-family: monospace;">' . $quotation->quotation_number . '</strong>'],
            ['Customer', $quotation->customer?->name],
            ['Total', '<strong>' . $symbol . ' ' . number_format((float) $quotation->total, 0, ',', '.') . '</strong>'],
            ['Ditolak', $quotation->rejected_at?->format('d F Y, H:i') . ' WIB'],
        ],
    ])

    @if($quotation->rejection_reason)
        <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #991b1b;">Alasan Penolakan</p>
            <p style="margin: 0; font-size: 14px; color: #7f1d1d; line-height: 1.6; white-space: pre-line;">{{ $quotation->rejection_reason }}</p>
        </div>
    @endif

    @include('emails.partials.button', [
        'url' => url('/admin/quotations/' . $quotation->id),
        'label' => 'Review di Admin Panel',
    ])
@endsection
