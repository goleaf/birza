@props([
    'model' => null,
    'total' => 5,
    'shape' => null,
    'color' => null,
])

@php
    $ratingClasses = implode(' ', array_filter([
        $shape ? '!mask-' . $shape : null,
        $color ? 'bg-' . $color : null,
    ]));

    $ratingAttributes = $model
        ? $attributes->merge(['wire:model' => $model])
        : $attributes;
@endphp

<x-mary-rating
    {{ $ratingAttributes->class($ratingClasses) }}
    :total="$total"
/>
