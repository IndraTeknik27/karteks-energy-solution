@php
    /** @var \App\Models\User $user */
    /** @var array $data */
@endphp

@extends('emails.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <span class="email-badge email-badge-info" style="font-size: 13px; padding: 6px 16px;">
            🔔 Notifikasi Baru
        </span>
    </div>

    <p class="email-greeting">Halo {{ $user->name ?? 'Customer' }},</p>

    <p class="email-message">
        {{ $data['message'] ?? 'Anda memiliki notifikasi baru di KARTEKS.' }}
    </p>

    @if(! empty($data['action_url']))
        @include('emails.partials.button', [
            'url' => $data['action_url'],
            'label' => $data['action_label'] ?? 'Lihat Detail',
        ])
    @endif

    <p class="email-message" style="margin-top: 24px; font-size: 13px; color: #6b7280;">
        Notifikasi ini dikirim otomatis oleh sistem KARTEKS. Jika ada pertanyaan, balas email ini.
    </p>
@endsection