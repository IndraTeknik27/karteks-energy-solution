@php
    /**
     * Featured Products section partial.
     * Variables: $section (HomepageSection), $data (array with products Collection, columns int)
     */
    $products = $data['products'] ?? collect();
    $columns = (int) ($data['columns'] ?? 4);
    $gridCols = match ($columns) {
        3 => 'md:grid-cols-3',
        5 => 'md:grid-cols-5',
        default => 'md:grid-cols-2 lg:grid-cols-4',
    };
@endphp

@if($products->isNotEmpty())
    <section class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    @if($section->subtitle)
                        <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $section->subtitle }}</span>
                    @endif
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">{{ $section->title ?? 'Produk Pilihan' }}</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>

            <div class="grid grid-cols-2 {{ $gridCols }} gap-4 md:gap-6">
                @foreach($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>
@endif