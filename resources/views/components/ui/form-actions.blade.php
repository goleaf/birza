@props([
    'submitLabel',
    'cancelHref',
    'cancelLabel' => null,
    'submitTarget' => null,
])

@php
    $cancelLabel = is_string($cancelLabel) && $cancelLabel !== '' ? $cancelLabel : __('common_cancel');
@endphp

<div {{ $attributes->class('flex items-center justify-end gap-3') }}>
    <x-button
        type="submit"
        primary
        :label="$submitLabel"
        :spinner="$submitTarget"
        wire:loading.attr="disabled"
    />

    <x-button
        flat
        :href="$cancelHref"
        :label="$cancelLabel"
    />
</div>


