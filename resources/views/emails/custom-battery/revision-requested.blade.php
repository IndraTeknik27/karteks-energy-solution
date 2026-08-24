@php
    /** @var \App\Models\CustomBatteryRevision $revision */
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-warning" style="font-size: 13px; padding: 6px 16px;">
            📝 Revisi Diminta
        </span>
    </div>

    <p class="email-greeting">Halo {{ $revision->request->customer?->name ?? 'Customer' }},</p>
    <p class="email-message">
        Admin kami perlu informasi tambahan atau revisi untuk custom battery request Anda.
        Mohon baca catatan di bawah dan berikan klarifikasi.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Request',
        'rows' => [
            ['No. Request', '<strong style="font-family: monospace;">' . $revision->request->request_number . '</strong>'],
            ['Aplikasi', $revision->request->application],
        ],
    ])

    @if($revision->admin_note)
        <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0;">
            <p style="margin: 0 0 4px 0; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #92400e;">Catatan Admin</p>
            <p style="margin: 0; font-size: 14px; color: #78350f; line-height: 1.6; white-space: pre-line;">{{ $revision->admin_note }}</p>
        </div>
    @endif

    @if($revision->field_changes && count($revision->field_changes) > 0)
        <p class="email-message" style="margin-top: 16px;"><strong>Field yang perlu direvisi:</strong></p>
        <ul style="margin: 0 0 16px 0; padding-left: 20px; color: #374151; font-size: 14px;">
            @foreach($revision->field_changes as $field => $note)
                <li><strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong> {{ $note }}</li>
            @endforeach
        </ul>
    @endif

    @include('emails.partials.button', [
        'url' => url('/dashboard/custom-battery/' . $revision->request->id),
        'label' => 'Berikan Revisi',
    ])

    <p class="email-message" style="margin-top: 16px; font-size: 13px; color: #6b7280;">
        Revisi ke-{{ $revision->request->revision_count }}. Jika ada pertanyaan, balas email ini.
    </p>
@endsection
