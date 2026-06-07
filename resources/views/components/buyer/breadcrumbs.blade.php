@props(['items' => []])

<x-ui.breadcrumbs
    :items="array_merge([
        [
            'label' => __('common_dashboard'),
            'link' => route('buyer.dashboard'),
            'icon' => 'o-home',
        ],
    ], $items)"
    {{ $attributes }}
/>
