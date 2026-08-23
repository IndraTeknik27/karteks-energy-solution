@extends('layouts.app')

@section('title', $blog->meta_title ?? $blog->title)
@section('description', $blog->meta_description ?? $blog->excerpt ?? '')

@section('content')
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-brand-600">Beranda</a>
                <span class="mx-1.5">/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-brand-600">Blog</a>
                @if($blog->category)
                    <span class="mx-1.5">/</span>
                    <a href="{{ route('blog.index', ['category_slug' => $blog->category->slug]) }}" class="hover:text-brand-600">{{ $blog->category->name }}</a>
                @endif
            </nav>
        </div>
    </section>

    <article class="py-10 bg-white">
        <div class="container mx-auto px-4 sm:px-6 max-w-3xl">
            @if($blog->category)
                <a href="{{ route('blog.index', ['category_slug' => $blog->category->slug]) }}" class="text-xs uppercase tracking-wider text-brand-600 font-semibold">{{ $blog->category->name }}</a>
            @endif
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2 leading-tight">{{ $blog->title }}</h1>

            <div class="flex items-center gap-3 mt-4 text-sm text-gray-500">
                @if($blog->author)
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-semibold">
                            {{ strtoupper(substr($blog->author->name, 0, 1)) }}
                        </div>
                        <span>{{ $blog->author->name }}</span>
                    </div>
                    <span>•</span>
                @endif
                <span>{{ $blog->published_at?->format('d F Y') }}</span>
                <span>•</span>
                <span>{{ $blog->reading_time ?? 3 }} menit baca</span>
            </div>

            @if($blog->featured_image_url)
                <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" class="w-full rounded-2xl my-8">
            @endif

            <div class="prose prose-lg max-w-none text-gray-700">
                {!! $blog->content !!}
            </div>

            @if($blog->tags->isNotEmpty())
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-2">Tags</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($blog->tags as $tag)
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </article>

    @if($related->isNotEmpty())
        <section class="py-12 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 max-w-5xl">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Artikel Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($related as $rel)
                        <a href="{{ route('blog.show', $rel->slug) }}" class="group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-lg transition">
                            <div class="aspect-video bg-gray-100 overflow-hidden">
                                @if($rel->featured_image_url)
                                    <img src="{{ $rel->featured_image_url }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @endif
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-sm text-gray-900 line-clamp-2 group-hover:text-brand-700 transition">{{ $rel->title }}</h3>
                                <div class="text-xs text-gray-500 mt-2">{{ $rel->published_at?->format('d M Y') }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection