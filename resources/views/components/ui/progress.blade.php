@props([
    'value' => 0,
    'max' => 100,
    'color' => null,
    'indeterminate' => false,
])

@php
    $progressClasses = implode(' ', array_filter([
        $color ? 'progress-' . $color : null,
    ]));
@endphp

<x-mary-progress
    {{ $attributes->class($progressClasses) }}
    :value="$value"
    :max="$max"
    :indeterminate="$indeterminate"
/>
