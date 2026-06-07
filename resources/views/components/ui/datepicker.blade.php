@props([
    'label' => null,
    'icon' => 'o-calendar',
    'hint' => null,
    'clearable' => false,
    'inline' => false,
    'config' => [],
])

@php
    $datepickerConfig = array_merge(
        [
            'dateFormat' => 'Y-m-d',
        ],
        app()->getLocale() === 'lt' ? ['locale' => 'lt'] : [],
        $config,
    );
@endphp

@once
    @push('head-scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/lt.js"></script>
    @endpush
@endonce

<x-mary-datepicker
    {{ $attributes }}
    :label="$label"
    :icon="$icon"
    :hint="$hint"
    :clearable="$clearable"
    :inline="$inline"
    :config="$datepickerConfig"
/>
