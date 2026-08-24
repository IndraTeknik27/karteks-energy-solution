@extends('layouts.app')

@section('title', $product->name . ' - KARTEKS ENERGY SOLUTION')
@section('description', $product->short_description ?? Str::limit(strip_tags($product->description ?? ''), 160))

@section('content')
    {{-- Breadcrumb --}}
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-brand-600">Beranda</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('catalog.index') }}" class="hover:text-brand-600">Produk</a>
                @if($product->category)
                    <span class="mx-1.5">/</span>
                    <a href="{{ route('catalog.index', ['category_slug' => $product->category->slug]) }}" class="hover:text-brand-600">{{ $product->category->name }}</a>
                @endif
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">{{ $product->name }}</span>
            </nav>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

                {{-- Product Gallery --}}
                <div x-data="{ activeImage: 0 }">
                    @php $mainImage = $product->featuredImageUrl; @endphp
                    <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 mb-3">
                        @if($mainImage)
                            <img x-show="activeImage === 0" src="{{ $mainImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            </div>
                        @endif
                    </div>

                    @if($product->images->count() > 0)
                        <div class="grid grid-cols-5 gap-2">
                            @foreach($product->images->take(5) as $i => $image)
                                <button type="button" @click="activeImage = {{ $i }}" class="aspect-square rounded-lg overflow-hidden border-2 transition" :class="activeImage === {{ $i }} ? 'border-brand-500' : 'border-gray-200 hover:border-brand-300'">
                                    <img src="{{ $image->image_url }}" alt="" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Product Info --}}
                <div>
                    @if($product->brand)
                        <a href="{{ route('catalog.index', ['brand_slug' => $product->brand->slug]) }}" class="text-xs uppercase tracking-wider text-brand-600 font-semibold hover:text-brand-700">
                            {{ $product->brand->name }}
                        </a>
                    @endif
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mt-2">{{ $product->name }}</h1>

                    {{-- Rating --}}
                    @if($reviewStats['total'] > 0)
                        <div class="flex items-center gap-2 mt-3">
                            <div class="flex items-center gap-0.5 text-accent-500">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($reviewStats['average']))
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="text-sm text-gray-600">{{ $reviewStats['average'] }} dari 5 • {{ $reviewStats['total'] }} review</span>
                        </div>
                    @endif

                    {{-- Price --}}
                    <div class="mt-6 flex items-baseline gap-3 flex-wrap">
                        @if($product->sale_price > 0 && $product->sale_price < $product->price)
                            <span class="text-3xl md:text-4xl font-bold text-brand-700">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
                            <span class="text-lg text-gray-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            @php $discount = round((1 - $product->sale_price / $product->price) * 100); @endphp
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Hemat {{ $discount }}%</span>
                        @else
                            <span class="text-3xl md:text-4xl font-bold text-brand-700">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    @if($product->short_description)
                        <p class="text-gray-700 mt-4 leading-relaxed">{{ $product->short_description }}</p>
                    @endif

                    {{-- Stock info --}}
                    @php
                        $stock = (int) $product->stock_qty - (int) ($product->reserved_qty ?? 0);
                    @endphp
                    <div class="mt-4 text-sm">
                        @if($product->manage_stock)
                            @if($stock > 0)
                                <span class="text-brand-700 font-medium">✓ Stok tersedia: {{ $stock }}</span>
                            @else
                                <span class="text-red-600 font-medium">✗ Stok habis</span>
                            @endif
                            @if($stock > 0 && $stock <= ($product->low_stock_threshold ?? 5))
                                <span class="text-amber-600"> • Stok terbatas, pesan sekarang!</span>
                            @endif
                        @else
                            <span class="text-gray-600">Tersedia</span>
                        @endif
                    </div>

                    {{-- Add to cart --}}
                    @auth
                        @if($stock > 0 || !$product->manage_stock)
                            <form method="POST" action="{{ route('cart.add') }}" class="mt-6 flex flex-col sm:flex-row gap-3">
                                @csrf
                                <input type="hidden" name="itemable_type" value="product">
                                <input type="hidden" name="itemable_id" value="{{ $product->id }}">

                                <div class="flex items-center border-2 border-gray-200 rounded-full overflow-hidden">
                                    <button type="button" onclick="document.getElementById('qty').value = Math.max(1, parseInt(document.getElementById('qty').value) - 1)" class="px-3 py-2 text-gray-500 hover:bg-gray-100">−</button>
                                    <input id="qty" type="number" name="qty" value="1" min="1" max="999" class="w-12 text-center border-0 focus:outline-none focus:ring-0">
                                    <button type="button" onclick="document.getElementById('qty').value = parseInt(document.getElementById('qty').value) + 1" class="px-3 py-2 text-gray-500 hover:bg-gray-100">+</button>
                                </div>

                                <button type="submit" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition shadow-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5h13M9 21a1 1 0 100-2 1 1 0 000 2zm9 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                                    Tambah ke Keranjang
                                </button>
                            </form>

                            <form method="POST" action="{{ route('dashboard.wishlist.toggle', $product) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-12 h-12 border-2 border-gray-200 text-gray-500 hover:border-red-300 hover:text-red-500 rounded-full transition" title="Tambah ke Wishlist" aria-label="Tambah ke Wishlist">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="mt-6 inline-flex items-center px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition shadow-lg">
                            Masuk untuk Membeli
                        </a>
                    @endauth

                    {{-- Features --}}
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Garansi Resmi</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Pengiriman ke Seluruh Indonesia</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Pembayaran Aman (Midtrans)</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m1-9a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Produk Original</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description / Specs tabs --}}
            <div class="mt-12 border-t border-gray-100 pt-8" x-data="{ tab: 'description' }">
                <div class="flex border-b border-gray-200 gap-1 mb-6 overflow-x-auto">
                    <button @click="tab = 'description'" :class="tab === 'description' ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 border-b-2 font-semibold whitespace-nowrap transition">
                        Deskripsi
                    </button>
                    @if($product->sku)
                        <button @click="tab = 'specs'" :class="tab === 'specs' ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 border-b-2 font-semibold whitespace-nowrap transition">
                            Spesifikasi
                        </button>
                    @endif
                    <button @click="tab = 'reviews'" :class="tab === 'reviews' ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 border-b-2 font-semibold whitespace-nowrap transition">
                        Ulasan ({{ $reviewStats['total'] }})
                    </button>
                </div>

                <div x-show="tab === 'description'" x-transition>
                    <div class="prose prose-sm max-w-none text-gray-700">
                        {!! $product->description ?: '<p class="text-gray-500">Belum ada deskripsi.</p>' !!}
                    </div>
                </div>

                <div x-show="tab === 'specs'" x-transition style="display: none">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        @if($product->sku)
                            <div class="flex justify-between p-3 bg-gray-50 rounded-lg"><dt class="font-semibold text-gray-700">SKU</dt><dd class="text-gray-900">{{ $product->sku }}</dd></div>
                        @endif
                        @if($product->category)
                            <div class="flex justify-between p-3 bg-gray-50 rounded-lg"><dt class="font-semibold text-gray-700">Kategori</dt><dd class="text-gray-900">{{ $product->category->name }}</dd></div>
                        @endif
                        @if($product->brand)
                            <div class="flex justify-between p-3 bg-gray-50 rounded-lg"><dt class="font-semibold text-gray-700">Brand</dt><dd class="text-gray-900">{{ $product->brand->name }}</dd></div>
                        @endif
                        @if($product->weight)
                            <div class="flex justify-between p-3 bg-gray-50 rounded-lg"><dt class="font-semibold text-gray-700">Berat</dt><dd class="text-gray-900">{{ $product->weight }} kg</dd></div>
                        @endif
                        @if($product->dimensions)
                            <div class="flex justify-between p-3 bg-gray-50 rounded-lg"><dt class="font-semibold text-gray-700">Dimensi</dt><dd class="text-gray-900">
                                @if(is_array($product->dimensions))
                                    {{ ($product->dimensions['length'] ?? '?') }} × {{ ($product->dimensions['width'] ?? '?') }} × {{ ($product->dimensions['height'] ?? '?') }} cm
                                @endif
                            </dd></div>
                        @endif
                    </dl>
                </div>

                <div x-show="tab === 'reviews'" x-transition style="display: none">
                    @if($reviews->isEmpty())
                        <p class="text-gray-500 text-sm">Belum ada ulasan untuk produk ini.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <div class="border border-gray-100 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold">
                                                {{ strtoupper(substr($review->customer->name ?? 'A', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $review->customer->name ?? 'Anonim' }}</div>
                                                @if($review->is_verified_purchase)
                                                    <span class="text-[10px] uppercase tracking-wider text-brand-600 font-semibold">Verified Purchase</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $review->approved_at?->diffForHumans() }}</div>
                                    </div>
                                    <div class="flex gap-0.5 text-accent-500 mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                            @endif
                                        @endfor
                                    </div>
                                    @if($review->title)
                                        <h4 class="font-semibold text-sm text-gray-900 mb-1">{{ $review->title }}</h4>
                                    @endif
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $review->content }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $reviews->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Related Products --}}
    @if($related->isNotEmpty())
        <section class="py-12 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Produk Terkait</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($related as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection