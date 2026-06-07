@props([
    'label' => null,
    'icon' => null,
    'rightIcon' => null,
    'href' => null,
    'link' => null,
    'spinner' => null,
    'primary' => false,
    'secondary' => false,
    'positive' => false,
    'negative' => false,
    'flat' => false,
    'outline' => false,
    'sm' => false,
    'xs' => false,
    'responsive' => false,
    'external' => false,
    'noWireNavigate' => null,
])

@php
    $resolvedLink = $link ?? $href;

    $normalizedIcon = filled($icon) && ! \Illuminate\Support\Str::startsWith($icon, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $icon
        : $icon;

    $normalizedRightIcon = filled($rightIcon) && ! \Illuminate\Support\Str::startsWith($rightIcon, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $rightIcon
        : $rightIcon;

    $colorClass = match (true) {
        $negative => 'btn-error',
        $positive => 'btn-success',
        $secondary => 'btn-secondary',
        $primary => 'btn-primary',
        default => null,
    };

    $sizeClass = match (true) {
        $xs => 'btn-xs',
        $sm => 'btn-sm',
        default => null,
    };

    $buttonClasses = implode(' ', array_filter([
        $colorClass,
        $flat ? 'btn-soft' : null,
        $outline ? 'btn-outline' : null,
        $sizeClass,
    ]));
@endphp

<x-mary-button
    {{ $attributes->merge(['class' => $buttonClasses]) }}
    :label="$label"
    :icon="$normalizedIcon"
    :icon-right="$normalizedRightIcon"
    :spinner="$spinner"
    :link="$resolvedLink"
    :external="$external"
    :responsive="$responsive"
    :no-wire-navigate="$resolvedLink ? ($noWireNavigate ?? true) : ($noWireNavigate ?? false)"
>
    {{ $slot }}
</x-mary-button>

