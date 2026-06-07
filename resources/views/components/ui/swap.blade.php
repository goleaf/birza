@props([
    'swapId' => null,
    'trueIcon' => 'check-circle',
    'falseIcon' => 'minus-circle',
    'iconSize' => 'h-5 w-5',
])

@php
    $normalizedTrueIcon = filled($trueIcon) && ! \Illuminate\Support\Str::startsWith($trueIcon, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $trueIcon
        : $trueIcon;

    $normalizedFalseIcon = filled($falseIcon) && ! \Illuminate\Support\Str::startsWith($falseIcon, ['o-', 's-', 'm-', 'c-'])
        ? 'o-' . $falseIcon
        : $falseIcon;
@endphp

<x-mary-swap
    {{ $attributes }}
    :id="$swapId"
    :true-icon="$normalizedTrueIcon"
    :false-icon="$normalizedFalseIcon"
    :icon-size="$iconSize"
>
    @isset($before)
        <x-slot:before>
            {{ $before }}
        </x-slot:before>
    @endisset

    @isset($after)
        <x-slot:after>
            {{ $after }}
        </x-slot:after>
    @endisset
</x-mary-swap>
