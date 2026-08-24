{{--
    Star Rating Component
    Usage:
        <x-rating :value="4.5" />
        <x-rating :value="4.5" :count="128" />
        <x-rating :value="4" :max="5" size="sm" />
--}}
@props([
    'value'  => 0,
    'count'  => null,
    'max'    => 5,
    'size'   => 'md',  // sm | md | lg
    'showValue' => true,
])

@php
$sizeMap = [
    'sm' => 'w-3 h-3',
    'md' => 'w-4 h-4',
    'lg' => 'w-5 h-5',
];
$starSize = $sizeMap[$size] ?? $sizeMap['md'];
$value = (float) $value;
@endphp

<div class="inline-flex items-center gap-1.5">
    <div class="flex items-center gap-0.5">
        @for($i = 1; $i <= $max; $i++)
            @if($i <= floor($value))
                {{-- Full star --}}
                <svg class="{{ $starSize }} text-accent-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/>
                </svg>
            @elseif($i - 0.5 <= $value)
                {{-- Half star --}}
                <svg class="{{ $starSize }} text-accent-500" viewBox="0 0 20 20">
                    <defs><linearGradient id="half"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#d1d5db"/></linearGradient></defs>
                    <path fill="url(#half)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/>
                </svg>
            @else
                {{-- Empty star --}}
                <svg class="{{ $starSize }} text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/>
                </svg>
            @endif
        @endfor
    </div>

    @if($showValue && $count !== null)
        <span class="text-sm text-gray-600">{{ number_format($value, 1) }} <span class="text-gray-400">({{ number_format($count) }})</span></span>
    @elseif($showValue)
        <span class="text-sm text-gray-600">{{ number_format($value, 1) }}</span>
    @endif
</div>
