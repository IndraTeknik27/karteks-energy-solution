{{--
    Button Component
    Usage:
        <x-button>Primary Button</x-button>
        <x-button variant="secondary">Secondary</x-button>
        <x-button variant="ghost">Ghost</x-button>
        <x-button variant="danger">Danger</x-button>
        <x-button size="sm">Small</x-button>
        <x-button size="lg">Large</x-button>
        <x-button loading>Loading</x-button>
        <x-button icon="cart">With Icon</x-button>
        <x-button href="/link">Link Button</x-button>
--}}
@props([
    'variant' => 'primary',  // primary | secondary | ghost | danger | outline | link
    'size'     => 'md',      // sm | md | lg
    'loading'  => false,
    'disabled' => false,
    'href'    => null,
    'icon'    => null,
    'iconPosition' => 'left', // left | right
    'fullWidth' => false,
    'type'    => 'button',
])

@php
$sizeMap = [
    'sm' => 'px-4 py-2 text-sm rounded-full',
    'md' => 'px-5 py-2.5 text-sm font-semibold rounded-full',
    'lg' => 'px-7 py-3.5 text-base font-semibold rounded-full',
    'xl' => 'px-8 py-4 text-base font-bold rounded-full',
];

$variantMap = [
    'primary'   => 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm hover:shadow-md',
    'secondary' => 'bg-gray-100 text-gray-800 hover:bg-gray-200',
    'ghost'     => 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300',
    'danger'    => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
    'outline'   => 'bg-transparent border-2 border-brand-600 text-brand-600 hover:bg-brand-50',
    'link'      => 'text-brand-600 hover:text-brand-700 hover:underline p-0 shadow-none',
];

$sizeClass = $sizeMap[$size] ?? $sizeMap['md'];
$variantClass = $variantMap[$variant] ?? $variantMap['primary'];
$widthClass = $fullWidth ? 'w-full justify-center' : '';
$disabledClass = ($disabled || $loading) ? 'opacity-60 cursor-not-allowed pointer-events-none' : '';
@endphp

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => "inline-flex items-center gap-2 $sizeClass $variantClass $widthClass $disabledClass transition font-medium"]) }}>
        @if($loading)
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
        @if($loading) disabled @endif
        {{ $attributes->merge(['class' => "inline-flex items-center gap-2 $sizeClass $variantClass $widthClass $disabledClass transition font-medium"]) }}>
        @if($loading)
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
