@props([
    'name' => null,
    'label' => null,
])

@php
    $normalizedName = filled($name) && ! \Illuminate\Support\Str::startsWith($name, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $name
        : $name;
@endphp

<x-mary-icon
    {{ $attributes }}
    :name="$normalizedName"
    :label="$label"
/>
