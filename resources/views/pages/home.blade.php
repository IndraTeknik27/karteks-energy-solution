@extends('layouts.app')

@section('title', 'KARTEKS ENERGY SOLUTION - Solusi Energi Terbarukan & Kendaraan Listrik')
@section('description', 'KARTEKS ENERGY SOLUTION - Solusi energi terbarukan, kendaraan listrik, custom battery, dan konsultasi profesional di Sulawesi Selatan.')

@section('content')
    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 text-white overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.15),transparent_60%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(16,185,129,0.4),transparent_50%)]"></div>

        <div class="container mx-auto px-4 sm:px-6 py-16 md:py-24 lg:py-32 relative">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-3 py-1 text-xs font-medium uppercase tracking-wider mb-6">
                        <span class="w-1.5 h-1.5 bg-accent-400 rounded-full animate-pulse"></span>
                        Energi Terbarukan
                    </span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        Solusi Energi
                        <span class="block text-accent-400">untuk Masa Depan</span>
                    </h1>
                    <p class="text-lg text-brand-50 leading-relaxed mb-8">
                        Dari kendaraan listrik hingga custom battery dan solar panel — KARTEKS menyediakan solusi energi terbarukan yang handal, efisien, dan berkelanjutan untuk bisnis dan rumah tangga Anda.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('catalog.index') }}" class="inline-flex items-center px-6 py-3 bg-white text-brand-700 font-semibold rounded-full hover:bg-brand-50 transition shadow-lg">
                            Lihat Produk
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('services.index') }}" class="inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/30 text-white font-semibold rounded-full hover:bg-white/20 transition">
                            Konsultasi Gratis
                        </a>
                    </div>

                    <div class="grid grid-cols-3 gap-6 mt-12 pt-8 border-t border-white/20 max-w-md">
                        <div>
                            <div class="text-2xl md:text-3xl font-bold">500+</div>
                            <div class="text-xs text-brand-100 mt-1">Pelanggan</div>
                        </div>
                        <div>
                            <div class="text-2xl md:text-3xl font-bold">50+</div>
                            <div class="text-xs text-brand-100 mt-1">Produk</div>
                        </div>
                        <div>
                            <div class="text-2xl md:text-3xl font-bold">5★</div>
                            <div class="text-xs text-brand-100 mt-1">Rating</div>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($featuredProducts->take(4) as $product)
                            <a href="{{ route('catalog.show', $product->slug) }}" class="group bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-4 hover:bg-white/20 transition">
                                <div class="aspect-square rounded-xl bg-white/20 mb-3 overflow-hidden flex items-center justify-center">
                                    @if($product->getFirstMediaUrl('images', 'thumb'))
                                        <img src="{{ $product->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-12 h-12 text-white/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    @endif
                                </div>
                                <div class="text-sm font-semibold line-clamp-2">{{ $product->name }}</div>
                                <div class="text-xs text-brand-100 mt-1">{{ $product->price_formatted ?? 'Rp ' . number_format($product->price, 0, ',', '.') }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Hero Banners from CMS --}}
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
                @foreach($topCategories as $category)
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
                                @default
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            @endswitch
                        </div>
                        <h3 class="font-semibold text-gray-900 text-sm mb-1">{{ $category->name }}</h3>
                        @if($category->products_count)
                            <p class="text-xs text-gray-500">{{ $category->products_count }} produk</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>

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

    {{-- FAQ --}}
    @if($faqs->isNotEmpty())
        <section class="py-12 md:py-16 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6">
                <div class="max-w-3xl mx-auto">
                    <div class="text-center mb-10">
                        <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">FAQ</span>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">Pertanyaan Umum</h2>
                    </div>

                    <div class="space-y-3" x-data="{ open: null }">
                        @foreach($faqs as $i => $faq)
                            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                                <button type="button" @click="open = (open === {{ $i }} ? null : {{ $i }})" class="w-full flex items-center justify-between p-5 text-left hover:bg-gray-50 transition">
                                    <span class="font-semibold text-gray-900 pr-4">{{ $faq->question }}</span>
                                    <svg class="w-5 h-5 text-gray-400 shrink-0 transition" :class="{ 'rotate-180': open === {{ $i }} }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open === {{ $i }}" x-collapse x-transition class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                                    {{ $faq->answer }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Latest Blog --}}
    @if($latestBlogs->isNotEmpty())
        <section class="py-12 md:py-16 bg-white">
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

    {{-- CTA Section --}}
    <section class="py-16 bg-gradient-to-r from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 text-center max-w-3xl">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Siap Beralih ke Energi Bersih?</h2>
            <p class="text-lg text-brand-50 mb-8">Konsultasikan kebutuhan energi Anda dengan tim ahli KARTEKS. Gratis, tanpa komitmen.</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('services.index') }}" class="inline-flex items-center px-6 py-3 bg-white text-brand-700 font-semibold rounded-full hover:bg-brand-50 transition">
                    Konsultasi Gratis
                </a>
                @if(config('karteks.company.whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('karteks.company.whatsapp')) }}?text=Halo%20KARTEKS%2C%20saya%20tertarik%20dengan%20produk%20Anda" target="_blank" class="inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/30 font-semibold rounded-full hover:bg-white/20 transition">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.591 1.746 5.522l-1.453 5.31 5.196-1.531zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        Chat WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection