@props([
    'title' => null,
    'borderless' => false,
    'shadowless' => false,
])

<x-card
    {{ $attributes }}
    :title="$title"
    :borderless="$borderless"
    :shadowless="$shadowless"
>
    @isset($actions)
        <x-slot:action>
            {{ $actions }}
        </x-slot:action>
    @endisset

    {{ $slot }}

    @isset($footer)
        <x-slot:footer>
            {{ $footer }}
        </x-slot:footer>
    @endisset
</x-card>


