@props([
    'title' => null,
    'subtitle' => null,
    'separator' => true,
    'progressIndicator' => false,
])

<x-mary-header
    {{ $attributes }}
    :title="$title"
    :subtitle="$subtitle"
    :separator="$separator"
    :progress-indicator="$progressIndicator"
>
    @isset($actions)
        <x-slot:actions>
            {{ $actions }}
        </x-slot:actions>
    @endisset
</x-mary-header>
