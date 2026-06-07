@props(['items' => []])

<x-ui.breadcrumbs
    :items="array_merge([
        [
            'label' => __('backend_dashboard_title'),
            'link' => route('backend.dashboard'),
            'icon' => 'o-home',
        ],
    ], $items)"
    {{ $attributes }}
/>
