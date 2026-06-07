@props(['items' => []])

<x-ui.breadcrumbs
    :items="array_merge([
        [
            'label' => __('common_dashboard'),
            'link' => route('seller.dashboard'),
            'icon' => 'o-home',
        ],
    ], $items)"
    {{ $attributes }}
/>
