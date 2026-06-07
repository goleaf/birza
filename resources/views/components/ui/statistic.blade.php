@props([
    'title' => null,
    'description' => null,
    'value' => null,
    'icon' => null,
    'color' => '',
    'tooltip' => null,
    'tooltipLeft' => null,
    'tooltipRight' => null,
    'tooltipBottom' => null,
])

@php
    $normalizedIcon = filled($icon) && ! \Illuminate\Support\Str::startsWith($icon, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $icon
        : $icon;
@endphp

<x-mary-stat
    {{ $attributes }}
    :title="$title"
    :description="$description"
    :value="$value"
    :icon="$normalizedIcon"
    :color="$color"
    :tooltip="$tooltip"
    :tooltip-left="$tooltipLeft"
    :tooltip-right="$tooltipRight"
    :tooltip-bottom="$tooltipBottom"
>
    {{ $slot }}
</x-mary-stat>
