@php
    /** @var \App\Models\CustomBatteryRequest $request */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-info" style="font-size: 13px; padding: 6px 16px;">
            🔋 Custom Battery Request Baru
        </span>
    </div>

    <p class="email-greeting">Halo Tim KARTEKS,</p>
    <p class="email-message">
        Ada permintaan custom battery baru yang masuk dan menunggu review dari admin.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Customer',
        'rows' => [
            ['No. Request', '<strong style="font-family: monospace;">' . $request->request_number . '</strong>'],
            ['Customer', $request->customer?->name ?? 'Guest'],
            ['Email', $request->customer?->email ?? '—'],
        ],
    ])

    @include('emails.partials.info-card', [
        'title' => 'Spesifikasi Battery',
        'rows' => [
            ['Kimia', $request->chemistry],
            ['Voltage', $request->voltage],
            ['Kapasitas', $request->capacity],
            ['Kebutuhan Energi', $request->kwh ? $request->kwh . ' kWh' : '—'],
            ['Aplikasi', $request->application],
            ['Current Load', $request->current_load ?: '—'],
            ['Quantity', $request->quantity . ' unit'],
            ['Deadline', $request->deadline?->format('d F Y') ?? '—'],
        ],
    ])

    @if($request->description)
        <p class="email-message" style="margin-top: 16px;"><strong>Deskripsi:</strong></p>
        <div style="background-color: #f9fafb; border-left: 4px solid #10b981; padding: 16px; margin: 8px 0 24px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.6; white-space: pre-line;">{{ $request->description }}</p>
        </div>
    @endif

    @include('emails.partials.button', [
        'url' => url('/admin/custom-battery-requests/' . $request->id),
        'label' => 'Review di Admin Panel',
    ])
@endsection
