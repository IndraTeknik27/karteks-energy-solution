@php
    /**
     * Blog Highlights section partial.
     * Variables: $section (HomepageSection), $data (array with blogs Collection)
     */
    $blogs = $data['blogs'] ?? collect();
@endphp

@if($blogs->isNotEmpty())
    <section class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                <div>
                    @if($section->subtitle)
                        <span class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $section->subtitle }}</span>
                    @endif
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mt-1">{{ $section->title ?? 'Tips & Berita Terbaru' }}</h2>
                </div>
                <a href="{{ route('blog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua →</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($blogs as $blog)
                    <a href="{{ route('blog.show', $blog->slug) }}" class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-lg transition">
                        <div class="aspect-video bg-gray-100 overflow-hidden">
                            @if($blog->featured_image_url)
                                <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
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