@extends('layouts.app')

@section('title', 'Blog & Tips - KARTEKS ENERGY SOLUTION')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10">
            <h1 class="text-3xl md:text-4xl font-bold">Blog &amp; Tips</h1>
            <p class="text-brand-100 mt-1">Berita, tips, dan informasi seputar energi terbarukan</p>
        </div>
    </section>

    <section class="py-8 md:py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                @if($categories->isNotEmpty())
                    <aside>
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-900 mb-3">Kategori</h3>
                            <ul class="space-y-2 text-sm">
                                @foreach($categories as $cat)
                                    <li>
                                        <a href="{{ route('blog.index', ['category_slug' => $cat->slug]) }}" class="flex items-center justify-between py-1 text-gray-700 hover:text-brand-600">
                                            <span>{{ $cat->name }}</span>
                                            <span class="text-xs text-gray-400">({{ $cat->blogs_count }})</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </aside>
                @endif

                <div class="{{ $categories->isNotEmpty() ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                    @if($blogs->isEmpty())
                        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
                            <p class="text-gray-500">Belum ada artikel.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($blogs as $blog)
                                <a href="{{ route('blog.show', $blog->slug) }}" class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-lg transition">
                                    <div class="aspect-video bg-gray-100 overflow-hidden">
                                        @if($blog->featured_image_url)
                                            <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-5">
                                        @if($blog->category)
                                            <span class="text-[10px] uppercase tracking-wider text-brand-600 font-semibold">{{ $blog->category->name }}</span>
                                        @endif
                                        <h3 class="font-bold text-gray-900 mt-2 line-clamp-2 group-hover:text-brand-700 transition">{{ $blog->title }}</h3>
                                        @if($blog->excerpt)
                                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $blog->excerpt }}</p>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-3">{{ $blog->published_at?->format('d M Y') }} • {{ $blog->reading_time ?? 3 }} menit</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-8">{{ $blogs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection