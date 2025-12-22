@props([
    'label' => null,
    'icon' => null,
    'rightIcon' => null,
])

<x-button
    {{ $attributes }}
    :label="$label"
    :icon="$icon"
    :right-icon="$rightIcon"
>
    {{ $slot }}
</x-button>


