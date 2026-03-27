@props([
    'title' => null,
    'description' => null,
])

<x-ui.page
    :title="$title"
    :description="$description"
    {{ $attributes }}
>
    @isset($actions)
        <x-slot:actions>
            {{ $actions }}
        </x-slot:actions>
    @endisset

    {{ $slot }}
</x-ui.page>
