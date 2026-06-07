@props([
    'images' => [],
    'withArrows' => false,
    'withIndicators' => false,
])

@once
    @push('head-scripts')
        <script src="https://cdn.jsdelivr.net/npm/photoswipe@5.4.4/dist/umd/photoswipe.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/photoswipe@5.4.4/dist/umd/photoswipe-lightbox.umd.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/photoswipe@5.4.4/dist/photoswipe.min.css" rel="stylesheet">
    @endpush
@endonce

<x-mary-image-gallery
    {{ $attributes }}
    :images="$images"
    :with-arrows="$withArrows"
    :with-indicators="$withIndicators"
/>
