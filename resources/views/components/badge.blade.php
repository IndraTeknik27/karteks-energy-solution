{{--
    Badge Component
    Usage:
        <x-badge>Default</x-badge>
        <x-badge variant="success">Success</x-badge>
        <x-badge variant="warning">Warning</x-badge>
        <x-badge variant="danger">Danger</x-badge>
        <x-badge variant="info">Info</x-badge>
        <x-badge variant="brand">Brand</x-badge>
        <x-badge variant="discount">-20%</x-badge>
        <x-badge size="sm">Small</x-badge>
--}}
@props([
    'variant' => 'default', // default | success | warning | danger | info | brand | discount | gray
    'size'     => 'md',     // sm | md | lg
])

@php
$sizeMap = [
    'sm' => 'px-1.5 py-0.5 text-[10px]',
    'md' => 'px-2.5 py-1 text-xs',
    'lg' => 'px-3 py-1 text-sm',
];

$variantMap = [
    'default'  => 'bg-gray-100 text-gray-700',
    'success'  => 'bg-emerald-100 text-emerald-800',
    'warning'  => 'bg-amber-100 text-amber-800',
    'danger'   => 'bg-red-100 text-red-800',
    'info'     => 'bg-blue-100 text-blue-800',
    'brand'    => 'bg-brand-100 text-brand-800',
    'discount' => 'bg-red-500 text-white',
    'gray'     => 'bg-gray-500/10 text-gray-600',
];

$sizeClass = $sizeMap[$size] ?? $sizeMap['md'];
$variantClass = $variantMap[$variant] ?? $variantMap['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-bold rounded-full uppercase tracking-wider $sizeClass $variantClass"]) }}>
    {{ $slot }}
</span>
