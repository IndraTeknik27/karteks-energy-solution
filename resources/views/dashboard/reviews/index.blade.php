@extends('layouts.app')

@section('title', 'Ulasan Saya - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-3xl font-bold">Ulasan Saya</h1>
            <p class="text-brand-100 mt-1">{{ $reviews->total() }} ulasan yang telah Anda tulis</p>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-4xl">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($reviews->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                    <h2 class="mt-4 text-xl font-bold text-gray-900">Belum ada ulasan</h2>
                    <p class="text-gray-500 mt-2">Beli produk dan berikan ulasan setelah pesanan diterima.</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block mt-6 px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Mulai Belanja</a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($reviews as $review)
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <div class="flex gap-4">
                                <div class="w-16 h-16 bg-gray-50 rounded-xl overflow-hidden shrink-0">
                                    @if($review->product?->getFirstMediaUrl('images', 'thumb'))
                                        <img src="{{ $review->product->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $review->product->name }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div>
                                            <a href="{{ route('catalog.show', $review->product?->slug) }}" class="font-semibold text-gray-900 hover:text-brand-700">{{ $review->product?->name ?? 'Produk dihapus' }}</a>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $review->created_at?->format('d M Y') }}</div>
                                        </div>
                                        <div class="flex items-center gap-2 text-amber-500 shrink-0">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    @if($review->title)
                                        <h4 class="font-semibold text-sm text-gray-900">{{ $review->title }}</h4>
                                    @endif
                                    <p class="text-sm text-gray-700 leading-relaxed mt-1">{{ $review->content }}</p>
                                    <div class="mt-3">
                                        <a href="{{ route('dashboard.review.edit', $review) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Edit Ulasan →</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $reviews->links() }}</div>
            @endif
        </div>
    </section>
@endsection