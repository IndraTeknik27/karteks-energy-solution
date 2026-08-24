@php
    /**
     * SEO meta tags component.
     * Usage:
     *   <x-meta :model="$product" />
     *   <x-meta :meta="['title' => '...', 'description' => '...', 'image' => '...']" />
     *
     * Slot opsional: <x-slot:head> untuk additional head elements (JSON-LD, dst.)
     */
    $meta = $meta ?? app(\App\Services\V1\SeoService::class)->generateMeta($model ?? null);
    $title = $meta['title'] ?? config('karteks.company.name');
    $description = $meta['description'] ?? '';
    $keywords = $meta['keywords'] ?? null;
    $canonical = $meta['canonical'] ?? url()->current();
    $image = $meta['image'] ?? null;
    $ogType = $meta['og_type'] ?? 'website';
    $twitterCard = $meta['twitter_card'] ?? 'summary_large_image';
    $siteName = $meta['site_name'] ?? config('karteks.company.name');
    $locale = $meta['locale'] ?? 'id_ID';
    $twitterHandle = $meta['twitter_handle'] ?? config('karteks.seo.twitter_handle');
@endphp

{{-- Primary Meta Tags --}}
<title>{{ $title }}</title>
<meta name="title" content="{{ $title }}">
<meta name="description" content="{{ $description }}">
@if($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif
<meta name="author" content="{{ $siteName }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ $locale }}">
@if($image)
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:alt" content="{{ $title }}">
@endif

{{-- Product-specific OG --}}
@if(($ogType ?? null) === 'product')
    @if(! empty($meta['product:price:amount']))
        <meta property="product:price:amount" content="{{ $meta['product:price:amount'] }}">
        <meta property="product:price:currency" content="{{ $meta['product:price:currency'] }}">
    @endif
    @if(! empty($meta['product:availability']))
        <meta property="product:availability" content="{{ $meta['product:availability'] }}">
    @endif
@endif

{{-- Article-specific OG --}}
@if(($ogType ?? null) === 'article')
    @if(! empty($meta['article:published_time']))
        <meta property="article:published_time" content="{{ $meta['article:published_time'] }}">
    @endif
    @if(! empty($meta['article:author']))
        <meta property="article:author" content="{{ $meta['article:author'] }}">
    @endif
    @if(! empty($meta['article:section']))
        <meta property="article:section" content="{{ $meta['article:section'] }}">
    @endif
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:url" content="{{ $canonical }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@if($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif
@if($twitterHandle)
    <meta name="twitter:site" content="{{ $twitterHandle }}">
    <meta name="twitter:creator" content="{{ $twitterHandle }}">
@endif

{{-- Mobile / Theme --}}
<meta name="theme-color" content="#10b981">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">