@props([
    'events' => [],
    'months' => 1,
    'locale' => null,
    'weekendHighlight' => true,
    'sundayStart' => false,
    'config' => [],
])

@php
    $calendarLocale = $locale ?? match (app()->getLocale()) {
        'lt' => 'lt-LT',
        default => 'en-US',
    };
@endphp

@once
    @push('head-scripts')
        <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@3.0.8/index.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@3.0.8/styles/index.min.css" rel="stylesheet">
    @endpush
@endonce

<div {{ $attributes }}>
    <x-mary-calendar
        :events="$events"
        :months="$months"
        :locale="$calendarLocale"
        :weekend-highlight="$weekendHighlight"
        :sunday-start="$sundayStart"
        :config="$config"
    />
</div>
