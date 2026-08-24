{{--
    Section Wrapper Component
    Usage:
        <x-section title="Featured Products" subtitle="Best sellers this week">
            Content here
        </x-section>

        <x-section title="Our Services" variant="dark">
            Content
        </x-section>

        <x-section title="Blog" align="center">
            Content
        </x-section>
--}}
@props([
    'title'    => null,
    'subtitle' => null,
    'variant'  => 'light', // light | dark | brand | gray
    'align'    => 'left',  // left | center
    'eyebrow'  => null,    // small eyebrow label above title
    'action'   => null,    // slot for action button/link on the right
])

@php
$variantClasses = [
    'light' => 'bg-white text-gray-900',
    'gray'  => 'bg-gray-50 text-gray-900',
    'brand' => 'bg-brand-600 text-white',
    'dark'  => 'bg-gray-900 text-white',
];

$alignClasses = [
    'left'   => 'text-left',
    'center' => 'text-center',
];

$variantClass = $variantClasses[$variant] ?? $variantClasses['light'];
$alignClass = $alignClasses[$align] ?? $alignClasses['left'];
$hasHeader = $title || $subtitle || $eyebrow;
@endphp

<section {{ $attributes->merge(['class' => "py-12 md:py-16 $variantClass"]) }}>
    <div class="container mx-auto px-4 sm:px-6">
        @if($hasHeader)
            <div class="{{ $alignClass }} @if($action) flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 @endif">
                <div class="{{ $alignClass === 'text-center' ? 'mx-auto' : '' }}">
                    @if($eyebrow)
                        <span class="inline-block text-xs uppercase tracking-widest font-semibold @if($variant === 'brand' || $variant === 'dark') text-white/60 @else text-brand-600 @endif mb-3">
                            {{ $eyebrow }}
                        </span>
                    @endif
                    @if($title)
                        <h2 class="text-2xl md:text-3xl font-bold @if($variant === 'brand' || $variant === 'dark') text-white @else text-gray-900 @endif">
                            {{ $title }}
                        </h2>
                    @endif
                    @if($subtitle)
                        <p class="mt-2 text-gray-600 @if($variant === 'brand' || $variant === 'dark') text-white/70 @endif max-w-2xl @if($align === 'center') mx-auto @endif">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
                @if($action)
                    <div class="shrink-0">{{ $action }}</div>
                @endif
            </div>
            @if($title || $eyebrow)
                @if($align === 'center')
                    <div class="w-12 h-1 bg-brand-500 rounded-full mt-6 mx-auto @if($variant === 'brand' || $variant === 'dark') block @elseif(!$title) hidden @endif"></div>
                @endif
            @endif
        @endif

        {{ $slot }}
    </div>
</section>
