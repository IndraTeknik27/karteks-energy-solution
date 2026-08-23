@extends('layouts.app')

@section('title', 'Edit Ulasan')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('catalog.show', $product->slug) }}" class="hover:text-brand-600">{{ $product->name }}</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-900">Edit Ulasan</span>
            </nav>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">
            @if(session('success'))
                <div class="mb-4 p-3 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <form method="POST" action="{{ route('dashboard.review.update', $review) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rating <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-1" x-data="{ rating: {{ old('rating', $review->rating) }}, hover: 0 }">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    @click="rating = {{ $i }}"
                                    @mouseover="hover = {{ $i }}"
                                    @mouseleave="hover = 0"
                                    :class="(hover || rating) >= {{ $i }} ? 'text-amber-500' : 'text-gray-300'"
                                    class="focus:outline-none transition">
                                    <svg class="w-9 h-9" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                </button>
                            @endfor
                            <input type="hidden" name="rating" :value="rating" x-model="rating" required>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Judul Ulasan</label>
                        <input type="text" name="title" value="{{ old('title', $review->title) }}" maxlength="120"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ulasan Lengkap <span class="text-red-500">*</span></label>
                        <textarea name="content" rows="6" required minlength="10" maxlength="2000" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">{{ old('content', $review->content) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex gap-3">
                            <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-full hover:bg-brand-700 transition">Simpan Perubahan</button>
                            <a href="{{ route('dashboard.review.my') }}" class="px-6 py-3 text-gray-600 hover:text-gray-900 font-semibold">Batal</a>
                        </div>
                        <form method="POST" action="{{ route('dashboard.review.destroy', $review) }}" onsubmit="return confirm('Hapus ulasan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Hapus Ulasan</button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection