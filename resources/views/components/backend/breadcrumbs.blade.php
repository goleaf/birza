@props(['items' => []])

<x-ui.breadcrumbs
    :items="array_merge([
        [
            'label' => __('backend_dashboard_title'),
            'link' => route('admin.dashboard'),
            'icon' => 'o-home',
        ],
    ], $items)"
    {{ $attributes }}
/>
