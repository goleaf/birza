@props([
    'title' => null,
    'subtitle' => null,
    'items' => [],
])

@php
    $toneClasses = [
        'success' => ['connector' => 'border-s-success', 'bullet' => 'bg-success'],
        'error' => ['connector' => 'border-s-error', 'bullet' => 'bg-error'],
        'info' => ['connector' => 'border-s-info', 'bullet' => 'bg-info'],
        'warning' => ['connector' => 'border-s-warning', 'bullet' => 'bg-warning'],
        'neutral' => ['connector' => 'border-s-base-300', 'bullet' => 'bg-base-300'],
        'primary' => ['connector' => 'border-s-primary', 'bullet' => 'bg-primary'],
    ];
@endphp

<x-ui.card
    {{ $attributes }}
    :title="$title"
    :subtitle="$subtitle"
    separator
>
    <div class="space-y-0">
        @foreach ($items as $item)
            @php
                $tone = $toneClasses[$item['tone'] ?? 'primary'] ?? $toneClasses['primary'];
            @endphp

            <x-mary-timeline-item
                :title="__($item['title'])"
                :subtitle="$item['subtitle']"
                :description="__($item['description'])"
                :icon="$item['icon']"
                :pending="$item['pending']"
                :first="$loop->first"
                :last="$loop->last"
                :connector-active-class="$tone['connector']"
                :bullet-active-class="$tone['bullet']"
            />
        @endforeach
    </div>
</x-ui.card>
