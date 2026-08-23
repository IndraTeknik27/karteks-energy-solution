@extends('layouts.app')

@section('title', ($currentCategory?->name ?? 'Semua Produk') . ' - KARTEKS ENERGY SOLUTION')

@section('content')
    {{-- Page header --}}
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10 md:py-14">
            <nav class="text-sm text-brand-100 mb-2">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('catalog.index') }}" class="hover:text-white">Produk</a>
                @if($currentCategory)
                    <span class="mx-1.5">/</span>
                    <span class="text-white">{{ $currentCategory->name }}</span>
                @endif
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold">
                @if($currentCategory)
                    {{ $currentCategory->name }}
                    @if($currentCategory->description)
                        <p class="text-brand-100 font-normal text-base mt-2 max-w-2xl">{{ $currentCategory->description }}</p>
                    @endif
                @elseif($currentBrand)
                    Brand: {{ $currentBrand->name }}
                @else
                    Semua Produk
                @endif
            </h1>
            <p class="text-brand-100 mt-2">{{ $products->total() }} produk ditemukan</p>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                {{-- Sidebar filters --}}
                <aside class="lg:sticky lg:top-20 lg:self-start space-y-4">
                    <form method="GET" action="{{ route('catalog.index') }}" class="bg-white rounded-2xl p-5 border border-gray-100">
                        <h3 class="font-bold text-gray-900 mb-3">Cari</h3>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">

                        <h3 class="font-bold text-gray-900 mt-5 mb-3">Kategori</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="{{ route('catalog.index', request()->except('category_slug', 'category_id', 'page')) }}" class="flex items-center justify-between py-1 {{ !request('category_slug') && !request('category_id') ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600' }}">
                                    <span>Semua Kategori</span>
                                </a>
                            </li>
                            @foreach($categories as $cat)
                                <li>
                                    <a href="{{ route('catalog.index', array_merge(request()->except('category_slug', 'category_id', 'page'), ['category_slug' => $cat->slug])) }}" class="flex items-center justify-between py-1 {{ request('category_slug') === $cat->slug ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600' }}">
                                        <span>{{ $cat->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <h3 class="font-bold text-gray-900 mt-5 mb-3">Brand</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="{{ route('catalog.index', request()->except('brand_slug', 'brand_id', 'page')) }}" class="flex items-center py-1 {{ !request('brand_slug') && !request('brand_id') ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600' }}">
                                    Semua Brand
                                </a>
                            </li>
                            @foreach($brands as $brand)
                                <li>
                                    <a href="{{ route('catalog.index', array_merge(request()->except('brand_slug', 'brand_id', 'page'), ['brand_slug' => $brand->slug])) }}" class="flex items-center py-1 {{ request('brand_slug') === $brand->slug ? 'text-brand-600 font-semibold' : 'text-gray-700 hover:text-brand-600' }}">
                                        {{ $brand->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <h3 class="font-bold text-gray-900 mt-5 mb-3">Harga</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>

                        <button type="submit" class="w-full mt-5 px-4 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                            Terapkan Filter
                        </button>

                        @if(request()->anyFilled(['search', 'category_slug', 'category_id', 'brand_slug', 'brand_id', 'min_price', 'max_price']))
                            <a href="{{ route('catalog.index') }}" class="block text-center mt-2 text-xs text-gray-500 hover:text-brand-600">Reset semua filter</a>
                        @endif
                    </form>
                </aside>

                {{-- Products grid --}}
                <div class="lg:col-span-3">
                    {{-- Sort toolbar --}}
                    <div class="flex items-center justify-between mb-4 bg-white rounded-2xl border border-gray-100 px-4 py-3">
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">{{ $products->count() }}</span> produk ditampilkan
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Urutkan:</label>
                            <select name="sort" onchange="window.location.href = this.value" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="{{ route('catalog.index', array_merge(request()->all(), ['sort' => 'latest'])) }}" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="{{ route('catalog.index', array_merge(request()->all(), ['sort' => 'popular'])) }}" {{ request('sort') === 'popular' ? 'selected' : '' }}>Paling Dilihat</option>
                                <option value="{{ route('catalog.index', array_merge(request()->all(), ['sort' => 'price_asc'])) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                                <option value="{{ route('catalog.index', array_merge(request()->all(), ['sort' => 'price_desc'])) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                                <option value="{{ route('catalog.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                            </select>
                        </div>
                    </div>

                    @if($products->isEmpty())
                        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900">Produk tidak ditemukan</h3>
                            <p class="text-sm text-gray-500 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
                            <a href="{{ route('catalog.index') }}" class="inline-block mt-4 text-sm text-brand-600 hover:text-brand-700 font-semibold">Reset filter →</a>
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($products as $product)
                                @include('partials.product-card', ['product' => $product])
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-8">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection