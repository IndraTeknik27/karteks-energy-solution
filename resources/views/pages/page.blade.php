@extends('layouts.app')

@section('title', $page->meta_title ?? $page->title)
@section('description', $page->meta_description ?? '')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="container mx-auto px-4 sm:px-6 py-10">
            <h1 class="text-3xl md:text-4xl font-bold">{{ $page->title }}</h1>
        </div>
    </section>

    <article class="py-10 bg-white">
        <div class="container mx-auto px-4 sm:px-6 max-w-4xl">
            @if($page->featured_image_url)
                <img src="{{ $page->featured_image_url }}" alt="{{ $page->title }}" class="w-full rounded-2xl mb-8">
            @endif

            <div class="prose prose-lg max-w-none text-gray-700">
                {!! $page->content !!}
            </div>
        </div>
    </article>
@endsection