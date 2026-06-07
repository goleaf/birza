@props([
    'title' => null,
    'subtitle' => null,
    'separator' => false,
    'shadow' => false,
    'bodyClass' => '',
    'progressIndicator' => null,
    'borderless' => false,
    'shadowless' => false,
])

@php
    $resolvedShadow = $shadowless ? false : $shadow;
    $resolvedBodyClass = trim(implode(' ', array_filter([
        $bodyClass,
        isset($footer) ? 'space-y-5' : null,
    ])));
@endphp

<x-mary-card
    {{ $attributes }}
    :title="$title"
    :subtitle="$subtitle"
    :separator="$separator"
    :shadow="$resolvedShadow"
    :progress-indicator="$progressIndicator"
    :body-class="$resolvedBodyClass"
>
    @isset($menu)
        <x-slot:menu>
            {{ $menu }}
        </x-slot:menu>
    @endisset

    @isset($actions)
        <x-slot:actions>
            {{ $actions }}
        </x-slot:actions>
    @endisset

    @isset($figure)
        <x-slot:figure>
            {{ $figure }}
        </x-slot:figure>
    @endisset

    {{ $slot }}

    @isset($footer)
        <div class="border-t border-base-300 pt-5">
            {{ $footer }}
        </div>
    @endisset
</x-mary-card>

