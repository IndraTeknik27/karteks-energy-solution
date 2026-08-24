@props([
    'schema' => null,        // single array
    'schemas' => [],         // multiple schemas (array of arrays)
])

@php
    if ($schema) {
        $schemas = array_merge([$schema], $schemas);
    }
@endphp

@if(! empty($schemas))
    @foreach($schemas as $single)
        <script type="application/ld+json">{!! json_encode($single, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endif