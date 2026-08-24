@php
    /** @var \App\Models\CustomBatteryRequest $request */
    $symbol = config('karteks.locale.currency_symbol', 'Rp');
    $statusLabels = [
        'under_review' => ['Sedang Direview', 'warning'],
        'quoted' => ['Quotation Tersedia', 'primary'],
        'approved' => ['Disetujui', 'success'],
        'rejected' => ['Ditolak', 'danger'],
        'in_production' => ['Dalam Produksi', 'info'],
        'completed' => ['Selesai', 'success'],
    ];
    $label = $statusLabels[$request->status] ?? [ucfirst(str_replace('_', ' ', $request->status)), 'info'];
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-{{ $label[1] }}" style="font-size: 13px; padding: 6px 16px;">
            Status Update: {{ $label[0] }}
        </span>
    </div>

    <p class="email-greeting">Halo {{ $request->customer?->name ?? 'Customer' }},</p>
    <p class="email-message">
        Ada update terbaru untuk custom battery request Anda. Silakan cek detail di bawah ini.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Request',
        'rows' => [
            ['No. Request', '<strong style="font-family: monospace;">' . $request->request_number . '</strong>'],
            ['Status', '<span class="email-badge email-badge-' . $label[1] . '">' . $label[0] . '</span>'],
            ['Aplikasi', $request->application],
            ['Spesifikasi', $request->chemistry . ' · ' . $request->voltage . ' · ' . $request->capacity],
        ],
    ])

    @if($request->estimated_price && in_array($request->status, ['quoted', 'approved']))
        @include('emails.partials.info-card', [
            'title' => 'Penawaran Harga',
            'rows' => [
                ['Estimasi Harga', '<strong style="color: #047857; font-size: 16px;">' . $symbol . ' ' . number_format((float) $request->estimated_price, 0, ',', '.') . '</strong>'],
                ['Berlaku Sampai', $request->approved_at?->addDays(30)->format('d F Y') ?? '—'],
            ],
        ])
    @endif

    @if($request->status === 'rejected' && $request->admin_notes)
        <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #991b1b;">Alasan Penolakan</p>
            <p style="margin: 0; font-size: 14px; color: #7f1d1d; line-height: 1.6; white-space: pre-line;">{{ $request->admin_notes }}</p>
        </div>
    @endif

    @include('emails.partials.button', [
        'url' => url('/dashboard/custom-battery/' . $request->id),
        'label' => 'Lihat Detail Request',
    ])
@endsection
