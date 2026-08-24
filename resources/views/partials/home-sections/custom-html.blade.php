@php
    /**
     * Custom HTML section partial.
     * Variables: $section (HomepageSection), $data (array with html string)
     * CATATAN: HTML mentah — hanya admin yang bisa edit.
     */
    $html = $data['html'] ?? '';
@endphp

@if($html)
    <section class="py-6">
        {!! $html !!}
    </section>
@endif