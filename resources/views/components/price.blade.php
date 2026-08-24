{{--
    Price Display Component
    Usage:
        <x-price :price="250000" />
        <x-price :price="250000" :sale-price="199000" />
        <x-price :price="250000" :sale-price="199000" size="lg" />
        <x-price :price="250000" :sale-price="199000" show-discount />
--}}
@props([
    'price',
    'salePrice' => null,
    'size'   => 'md',    // sm | md | lg | xl
    'showDiscount' => false,
    'currency' => 'Rp',
    'locale'  => 'id_ID',
])

@php
$price = (float) $price;
$salePrice = $salePrice ? (float) $salePrice : null;
$hasDiscount = $salePrice && $salePrice > 0 && $salePrice < $price;
$displayPrice = $hasDiscount ? $salePrice : $price;
$discount = $hasDiscount ? round((1 - $salePrice / $price) * 100) : null;

$sizeMap = [
    'sm' => ['current' => 'text-sm font-bold', 'old' => 'text-xs', 'badge' => 'text-[10px]'],
    'md' => ['current' => 'text-xl font-bold', 'old' => 'text-sm', 'badge' => 'text-[10px]'],
    'lg' => ['current' => 'text-2xl font-bold', 'old' => 'text-base', 'badge' => 'text-xs'],
    'xl' => ['current' => 'text-3xl md:text-4xl font-bold', 'old' => 'text-lg', 'badge' => 'text-xs'],
];

$sizes = $sizeMap[$size] ?? $sizeMap['md'];
$formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
$formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 0);
@endphp

<div class="inline-flex items-center gap-2 flex-wrap">
    <span class="text-brand-700 {{ $sizes['current'] }}">
        {{ $currency }} {{ number_format($displayPrice, 0, ',', '.') }}
    </span>

    @if($hasDiscount)
        <span class="text-gray-400 line-through {{ $sizes['old'] }}">
            {{ $currency }} {{ number_format($price, 0, ',', '.') }}
        </span>

        @if($showDiscount)
            <span class="px-2 py-0.5 bg-red-100 text-red-700 font-bold rounded-full {{ $sizes['badge'] }}">
                Hemat {{ $discount }}%
            </span>
        @endif
    @endif
</div>
