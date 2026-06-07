@props([
    'value' => 0,
    'unit' => '%',
    'color' => null,
])

@php
    $progressClasses = implode(' ', array_filter([
        $color ? 'text-' . $color : null,
    ]));
@endphp

<x-mary-progress-radial
    {{ $attributes->class($progressClasses) }}
    :value="$value"
    :unit="$unit"
/>
