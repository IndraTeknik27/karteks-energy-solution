{{--
    Alert Component
    Usage:
        <x-alert type="success" title="Berhasil!">Pesanan berhasil dibuat.</x-alert>
        <x-alert type="error" title="Gagal" dismissible>Terjadi kesalahan.</x-alert>
        <x-alert type="warning">Warning message.</x-alert>
        <x-alert type="info">Info message.</x-alert>
--}}
@props([
    'type'    => 'info',    // success | error | warning | info
    'title'   => null,
    'dismissible' => false,
])

@php
$typeMap = [
    'success' => [
        'bg'    => 'bg-emerald-50',
        'border'=> 'border-emerald-200',
        'text'  => 'text-emerald-900',
        'icon'  => 'text-emerald-600',
        'svg'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'error' => [
        'bg'    => 'bg-red-50',
        'border'=> 'border-red-200',
        'text'  => 'text-red-900',
        'icon'  => 'text-red-600',
        'svg'   => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'warning' => [
        'bg'    => 'bg-amber-50',
        'border'=> 'border-amber-200',
        'text'  => 'text-amber-900',
        'icon'  => 'text-amber-600',
        'svg'   => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    ],
    'info' => [
        'bg'    => 'bg-blue-50',
        'border'=> 'border-blue-200',
        'text'  => 'text-blue-900',
        'icon'  => 'text-blue-600',
        'svg'   => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
];

$config = $typeMap[$type] ?? $typeMap['info'];
$bgClass = $config['bg'];
$borderClass = $config['border'];
$textClass = $config['text'];
$iconClass = $config['icon'];
$svgPath = $config['svg'];

$alertId = 'alert_' . uniqid();
@endphp

<div x-data="{ show: true }" x-show="show"
    {{ $attributes->merge(['class' => "rounded-xl border p-4 text-sm $bgClass $borderClass $textClass"]) }}>
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $svgPath }}"/></svg>
        <div class="flex-1 min-w-0">
            @if($title)
                <p class="font-semibold mb-0.5">{{ $title }}</p>
            @endif
            <div>{{ $slot }}</div>
        </div>
        @if($dismissible)
            <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>
</div>
