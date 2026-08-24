{{--
    Card Component
    Usage:
        <x-card>Content here</x-card>
        <x-card variant="bordered">Bordered card</x-card>
        <x-card variant="shadow">Shadow card</x-card>
        <x-card variant="elevated">Elevated card</x-card>
        <x-card padding="sm">Compact padding</x-card>
        <x-card padding="lg">Large padding</x-card>
--}}
@props([
    'variant' => 'default', // default | bordered | shadow | elevated | flat
    'padding' => 'md',       // none | sm | md | lg
    'hover' => false,
    'class' => '',
])

@php
$baseClasses = 'bg-white rounded-2xl transition';

$paddingMap = [
    'none' => '',
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8',
];

$variantMap = [
    'default'  => 'border border-gray-100',
    'bordered' => 'border-2 border-gray-200',
    'shadow'   => 'shadow-sm border border-gray-100',
    'elevated' => 'shadow-lg',
    'flat'     => '',
];

$paddingClass = $paddingMap[$padding] ?? $paddingMap['md'];
$variantClass = $variantMap[$variant] ?? $variantMap['default'];
$hoverClass = $hover ? 'hover:shadow-md hover:border-brand-200 cursor-pointer' : '';
@endphp

<div {{ $attributes->merge(['class' => "$baseClasses $variantClass $paddingClass $hoverClass $class"]) }}>
    {{ $slot }}
</div>
