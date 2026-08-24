@php
    /** @var \App\Models\ContactMessage $message */
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-warning" style="font-size: 13px; padding: 6px 16px;">
            📩 Pesan Baru Diterima
        </span>
    </div>

    <p class="email-message">
        Ada pesan baru masuk dari form kontak website. Mohon ditanggapi dalam 1×24 jam kerja.
    </p>

    @include('emails.partials.info-card', [
        'title' => 'Detail Pengirim',
        'rows' => [
            ['Nama', $contactMessage->name],
            ['Email', '<a href="mailto:' . $contactMessage->email . '" style="color: #10b981;">' . $contactMessage->email . '</a>'],
            ['Telepon', $contactMessage->phone ? '<a href="https://wa.me/' . preg_replace('/[^0-9]/', '', $contactMessage->phone) . '" style="color: #10b981;">' . $contactMessage->phone . '</a>' : '—'],
        ],
    ])

    @include('emails.partials.info-card', [
        'title' => 'Pesan',
        'rows' => [
            ['Subjek', $contactMessage->subject],
            ['Diterima', $contactMessage->created_at->format('d F Y, H:i') . ' WIB'],
            ['IP Address', $contactMessage->ip_address ?? '—'],
        ],
    ])

    <div style="background-color: #fffbeb; border: 1px solid #fcd34d; padding: 16px; margin: 16px 0; border-radius: 8px;">
        <p style="margin: 0; font-size: 14px; color: #78350f; line-height: 1.6; white-space: pre-line;">{{ $contactMessage->message }}</p>
    </div>

    @include('emails.partials.button', [
        'url' => url('/admin/contact-messages/' . $contactMessage->id),
        'label' => 'Buka di Admin Panel',
    ])

    <p class="email-message" style="margin-top: 24px; font-size: 13px; color: #6b7280;">
        Balas langsung via email dengan klik "Reply" — system akan otomatis set status ke "replied".
    </p>
@endsection
