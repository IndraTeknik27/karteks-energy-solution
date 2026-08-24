@php
    /**
     * Hero Banner section partial.
     * Variables: $section (HomepageSection), $data (array with banners, autoplay)
     */
    $banners = $data['banners'] ?? collect();
    $autoplay = $data['autoplay'] ?? true;
@endphp

@if($banners->isNotEmpty())
    <section class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 py-6">
            @if($banners->count() === 1)
                {{-- Single banner: full width card --}}
                @php $banner = $banners->first(); @endphp
                <a href="{{ $banner->link_url ?: '#' }}" target="{{ $banner->link_target ?? '_self' }}"
                    onclick="fetch('{{ route('public.banner.click', $banner->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).catch(()=>{})"
                    class="block relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 hover:shadow-lg transition group">
                    @if($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http'))
                        <div class="aspect-[16/5] sm:aspect-[16/4] overflow-hidden">
                            <img src="{{ $banner->desktop_image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                        </div>
                    @endif
                    <div class="p-6 sm:p-8 {{ $banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http') ? 'absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/70 via-black/30 to-transparent text-white' : '' }}">
                        @if($banner->subtitle)
                            <p class="text-xs uppercase tracking-wider {{ ($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http')) ? 'text-brand-100' : 'text-brand-700' }} font-semibold mb-2">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->title)
                            <h3 class="text-2xl sm:text-3xl font-bold mb-2">{{ $banner->title }}</h3>
                        @endif
                        @if($banner->description)
                            <p class="text-sm {{ ($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http')) ? 'text-white/90' : 'text-gray-700' }} max-w-xl">{{ Str::limit($banner->description, 150) }}</p>
                        @endif
                        @if($banner->link_text)
                            <span class="inline-flex items-center mt-4 px-5 py-2 bg-white text-brand-700 font-semibold rounded-full text-sm group-hover:bg-brand-50 transition">
                                {{ $banner->link_text }}
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                            </span>
                        @endif
                    </div>
                </a>
            @else
                {{-- Multiple banners: grid --}}
                <div class="grid grid-cols-1 md:grid-cols-{{ min($banners->count(), 3) }} gap-4">
                    @foreach($banners as $banner)
                        <a href="{{ $banner->link_url ?: '#' }}" target="{{ $banner->link_target ?? '_self' }}"
                            onclick="fetch('{{ route('public.banner.click', $banner->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).catch(()=>{})"
                            class="block relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 hover:shadow-lg transition group">
                            @if($banner->desktop_image_url && str_starts_with($banner->desktop_image_url, 'http'))
                                <div class="aspect-[16/9] sm:aspect-[16/10] overflow-hidden">
                                    <img src="{{ $banner->desktop_image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                                </div>
                            @endif
                            <div class="p-5">
                                @if($banner->subtitle)
                                    <p class="text-[10px] uppercase tracking-wider text-brand-700 font-semibold mb-1">{{ $banner->subtitle }}</p>
                                @endif
                                @if($banner->title)
                                    <h3 class="font-bold text-gray-900 mb-1 text-base">{{ $banner->title }}</h3>
                                @endif
                                @if($banner->description)
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($banner->description, 80) }}</p>
                                @endif
                                @if($banner->link_text)
                                    <span class="inline-flex items-center text-sm font-semibold text-brand-700 mt-3">
                                        {{ $banner->link_text }}
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif