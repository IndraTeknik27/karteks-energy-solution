@php
    /**
     * Category Showcase section partial (EV Car, EV Bike).
     * Variables: $section (HomepageSection), $data (array with category, products)
     */
    $category = $data['category'] ?? null;
    $products = $data['products'] ?? collect();

    // Icon per category (override di setting jika perlu)
    $icon = match ($category?->icon) {
        'car' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M8 17a2 2 0 100-4 2 2 0 000 4zm8 0a2 2 0 100-4 2 2 0 000 4zm-9-4l1.5-5h11l1.5 5M4 13h16l1 4H3l1-4z"/></svg>',
        'motorcycle' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="5" cy="17" r="3"/><circle cx="19" cy="17" r="3"/><path d="M14 17H8l3-9h5l3 4"/></svg>',
        default => '<svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
    };
@endphp

@if($category || $products->isNotEmpty())
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600">
                        {!! $icon !!}
                    </div>
                    <div>
                        @if($section->subtitle)
                            <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $section->subtitle }}</span>
                        @endif
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">{{ $section->title ?? $category?->name }}</h2>
                    </div>
                </div>
                @if($category)
                    <a href="{{ route('catalog.index', ['category_slug' => $category->slug]) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
                @endif
            </div>

            @if($products->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                    @foreach($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-2xl border border-gray-100">
                    <p class="text-gray-500">Belum ada produk di kategori ini.</p>
                </div>
            @endif
        </div>
    </section>
@endif