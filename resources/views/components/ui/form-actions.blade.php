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
    <button
        type="submit"
        class="btn btn-primary"
        wire:loading.attr="disabled"
        @if ($submitTarget) wire:target="{{ $submitTarget }}" @endif
    >
        @if ($submitTarget)
            <span class="loading loading-spinner loading-xs" wire:loading wire:target="{{ $submitTarget }}"></span>
        @endif
        {{ $submitLabel }}
    </button>

    <a href="{{ $cancelHref }}" class="btn btn-ghost">
        {{ $cancelLabel }}
    </a>
</div>

