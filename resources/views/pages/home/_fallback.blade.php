{{--
    Fallback home page content - shown when no sections di DB.
    Variables: $faqs (Collection)
--}}

@php
    use Illuminate\Support\Facades\DB;

    // Fallback data (lazy load, di-blok try/catch supaya tidak error)
    try {
        $heroBanners = \App\Models\Banner::active()->position(\App\Models\Banner::POSITION_HOME_HERO)->orderBy('sort')->limit(5)->get();
    } catch (\Throwable $e) {
        $heroBanners = collect();
    }

    try {
        $featuredCategories = \App\Models\Category::query()->active()->roots()
            ->withCount(['products' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('sort')->limit(8)->get();
    } catch (\Throwable $e) {
        $featuredCategories = collect();
    }

    try {
        $featuredProducts = \App\Models\Product::query()
            ->where('status', 'published')->where('is_featured', true)
            ->with(['category', 'brand'])->latest('published_at')->limit(8)->get();
    } catch (\Throwable $e) {
        $featuredProducts = collect();
    }

    try {
        $featuredServices = \App\Models\Service::query()->active()
            ->where('is_featured', true)->with('category')->orderBy('sort')->limit(6)->get();
    } catch (\Throwable $e) {
        $featuredServices = collect();
    }

    try {
        $testimonials = \App\Models\Testimonial::query()->active()
            ->orderByDesc('is_featured')->orderBy('sort')->limit(6)->get();
    } catch (\Throwable $e) {
        $testimonials = collect();
    }

    try {
        $latestBlogs = \App\Models\Blog::query()->published()
            ->with(['category', 'author'])->latest('published_at')->limit(3)->get();
    } catch (\Throwable $e) {
        $latestBlogs = collect();
    }
@endphp

{{-- Hero Banners --}}
@if($heroBanners->isNotEmpty())
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-{{ min($heroBanners->count(), 3) }} gap-4">
                @foreach($heroBanners as $banner)
                    <a href="{{ $banner->link_url ?: '#' }}" class="block bg-gradient-to-br from-brand-50 to-brand-100 rounded-2xl p-6 hover:shadow-lg transition">
                        @if($banner->title)
                            <h3 class="font-bold text-gray-900 mb-1">{{ $banner->title }}</h3>
                        @endif
                        @if($banner->subtitle)
                            <p class="text-sm text-brand-700 font-medium mb-2">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->description)
                            <p class="text-sm text-gray-600">{{ Str::limit($banner->description, 100) }}</p>
                        @endif
                        @if($banner->link_text)
                            <span class="inline-flex items-center text-sm font-semibold text-brand-700 mt-3">
                                {{ $banner->link_text }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Categories --}}
@if($featuredCategories->isNotEmpty())
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Kategori Produk</h2>
                    <p class="text-gray-500 mt-1">Jelajahi produk sesuai kebutuhan Anda</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($featuredCategories as $category)
                    <a href="{{ route('catalog.index', ['category_slug' => $category->slug]) }}" class="group bg-white rounded-2xl p-5 hover:shadow-lg hover:border-brand-200 border border-gray-100 transition text-center">
                        <div class="w-12 h-12 mx-auto mb-3 bg-brand-50 rounded-xl flex items-center justify-center text-brand-600 group-hover:bg-brand-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 text-sm mb-1">{{ $category->name }}</h3>
                        @if(isset($category->products_count))
                            <p class="text-xs text-gray-500">{{ $category->products_count }} produk</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Featured Products --}}
@if($featuredProducts->isNotEmpty())
    <section class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">Unggulan</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">Produk Pilihan</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach($featuredProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Services --}}
@if($featuredServices->isNotEmpty())
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">Layanan</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">Layanan Profesional</h2>
                </div>
                <a href="{{ route('services.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($featuredServices as $service)
                    <a href="{{ route('services.show', $service->slug) }}" class="group bg-white rounded-2xl p-6 hover:shadow-lg transition border border-gray-100">
                        <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-4 group-hover:bg-brand-600 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.444L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.444l1.745-1.444"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">{{ $service->name }}</h3>
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $service->short_description }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-brand-600">{{ $service->price_label ?? 'Hubungi Kami' }}</span>
                            <span class="text-brand-600 group-hover:translate-x-1 transition">→</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Testimonials --}}
@if($testimonials->isNotEmpty())
    <section class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">Testimoni</span>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">Apa Kata Pelanggan Kami</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($testimonials as $testimonial)
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <div class="flex items-center gap-1 text-accent-500 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $testimonial->rating)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                @endif
                            @endfor
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">"{{ $testimonial->content }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-semibold">
                                {{ strtoupper(substr($testimonial->customer_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-sm text-gray-900">{{ $testimonial->customer_name }}</div>
                                @if($testimonial->position || $testimonial->company)
                                    <div class="text-xs text-gray-500">{{ trim(($testimonial->position ?? '') . ($testimonial->company ? ' • ' . $testimonial->company : '')) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Blog --}}
@if($latestBlogs->isNotEmpty())
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">Blog</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">Tips &amp; Berita Terbaru</h2>
                </div>
                <a href="{{ route('blog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($latestBlogs as $blog)
                    <a href="{{ route('blog.show', $blog->slug) }}" class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-lg transition">
                        <div class="aspect-video bg-gray-100 overflow-hidden">
                            @if($blog->featured_image_url)
                                <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            @if($blog->category)
                                <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $blog->category->name }}</span>
                            @endif
                            <h3 class="font-bold text-gray-900 mt-2 line-clamp-2 group-hover:text-brand-700 transition">{{ $blog->title }}</h3>
                            @if($blog->excerpt)
                                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $blog->excerpt }}</p>
                            @endif
                            <div class="text-xs text-gray-500 mt-3">{{ $blog->published_at?->format('d M Y') }} • {{ $blog->reading_time ?? 3 }} menit baca</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif