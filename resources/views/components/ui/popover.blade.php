@props([
    'position' => 'bottom',
    'offset' => 12,
])

<x-mary-popover
    {{ $attributes }}
    :position="$position"
    :offset="$offset"
>
    @isset($trigger)
        <x-slot:trigger>
            {{ $trigger }}
        </x-slot:trigger>
    @endisset

    @isset($content)
        <x-slot:content>
            <div class="w-72 rounded-2xl border border-base-200 bg-base-100 p-4 text-sm shadow-xl">
                {{ $content }}
            </div>
        </x-slot:content>
    @endisset
</x-mary-popover>
