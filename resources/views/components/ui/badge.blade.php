@props([
    'value' => null,
    'icon' => null,
    'rightIcon' => null,
    'color' => 'neutral',
    'soft' => false,
    'outline' => false,
    'dash' => false,
    'sm' => false,
])

@php
    $normalizedIcon = filled($icon) && ! \Illuminate\Support\Str::startsWith($icon, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $icon
        : $icon;

    $normalizedRightIcon = filled($rightIcon) && ! \Illuminate\Support\Str::startsWith($rightIcon, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $rightIcon
        : $rightIcon;

    $badgeClasses = implode(' ', array_filter([
        $color ? 'badge-' . $color : null,
        $soft ? 'badge-soft' : null,
        $outline ? 'badge-outline' : null,
        $dash ? 'badge-dash' : null,
        $sm ? 'badge-sm' : null,
    ]));
@endphp

<x-mary-badge
    {{ $attributes->merge(['class' => $badgeClasses]) }}
    :value="$value"
    :icon="$normalizedIcon"
    :icon-right="$normalizedRightIcon"
>
    {{ $slot }}
</x-mary-badge>
