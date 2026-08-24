@php
    /**
     * Featured Categories section partial.
     * Variables: $section (HomepageSection), $data (array with categories Collection)
     */
    $categories = $data['categories'] ?? collect();
    $showCount = (bool) $section->getSetting('show_product_count', true);
@endphp

@if($categories->isNotEmpty())
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    @if($section->subtitle)
                        <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $section->subtitle }}</span>
                    @endif
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">{{ $section->title ?? 'Kategori Produk' }}</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('catalog.index', ['category_slug' => $category->slug]) }}" class="group bg-white rounded-2xl p-5 hover:shadow-lg hover:border-brand-200 border border-gray-100 transition text-center">
                        <div class="w-12 h-12 mx-auto mb-3 bg-brand-50 rounded-xl flex items-center justify-center text-brand-600 group-hover:bg-brand-100 transition">
                            @switch($category->icon ?? '')
                                @case('car')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M8 17a2 2 0 100-4 2 2 0 000 4zm8 0a2 2 0 100-4 2 2 0 000 4zm-9-4l1.5-5h11l1.5 5M4 13h16l1 4H3l1-4z"/></svg>
                                    @break
                                @case('motorcycle')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="5" cy="17" r="3"/><circle cx="19" cy="17" r="3"/><path d="M14 17H8l3-9h5l3 4"/></svg>
                                    @break
                                @case('battery')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="7" width="18" height="10" rx="2"/><path d="M22 11v2M6 11h4M6 13h4"/></svg>
                                    @break
                                @case('sun')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                                    @break
                                @case('leaf')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 21c3-3 9-3 12-6s3-9 0-12c-3 0-9 3-12 6s-3 9 0 12z"/></svg>
                                    @break
                                @case('bolt')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                    @break
                                @case('wrench')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                                    @break
                                @case('cog')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M10.325 4.317a1.724 1.724 0 013.35 0c.426 1.756 2.924 1.756 3.35 0a1.724 1.724 0 012.573-1.066c1.543.94 3.31.826 4.36-.624 1.04-1.45 1.05-3.22.024-4.68-1.026-1.46-2.787-1.574-4.34-.645a1.724 1.724 0 00-2.573 1.066c-.426-1.756-2.924-1.756-3.35 0a1.724 1.724 0 01-2.573 1.066c-1.543-.94-3.31-.826-4.36.624-1.04 1.45-1.05 3.22-.024 4.68 1.026 1.46 2.787 1.574 4.34.645a1.724 1.724 0 002.573-1.066z"/><circle cx="12" cy="12" r="3"/></svg>
                                    @break
                                @default
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            @endswitch
                        </div>
                        <h3 class="font-semibold text-gray-900 text-sm mb-1">{{ $category->name }}</h3>
                        @if($showCount && isset($category->products_count))
                            <p class="text-xs text-gray-500">{{ $category->products_count }} produk</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif