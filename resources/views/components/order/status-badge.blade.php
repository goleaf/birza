@props([
    'order' => null,
    'status' => null,
    'sm' => false,
    'soft' => true,
])

@php
    $resolvedStatus = $status instanceof \App\Enums\OrderStatus
        ? $status
        : \App\Enums\OrderStatus::fromValue($status ?? $order?->status);
@endphp

<x-ui.badge
    :value="$resolvedStatus->label()"
    :color="$resolvedStatus->uiBadgeColor()"
    :icon="$resolvedStatus->icon()"
    :soft="$soft"
    :sm="$sm"
    {{ $attributes->class('font-semibold') }}
/>
