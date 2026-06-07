@props([
    'title' => null,
    'subtitle' => null,
    'items' => [],
    'panel' => [],
    'stepsColor' => 'step-primary',
])

@php
    $panelLabel = $panel['label'] ?? null;
    $panelDescription = $panel['description'] ?? null;
    $panelBadgeColor = $panel['badgeColor'] ?? 'neutral';
    $panelIcon = $panel['icon'] ?? 'o-clock';
@endphp

<x-ui.card
    {{ $attributes->whereDoesntStartWith('wire:model') }}
    :title="$title"
    :subtitle="$subtitle"
    separator
    body-class="space-y-5"
>
    <x-mary-steps
        {{ $attributes->whereStartsWith('wire:model') }}
        :steps-color="$stepsColor"
        stepper-classes="steps-vertical w-full gap-4 xl:steps-horizontal"
    >
        @foreach ($items as $item)
            <x-mary-step
                :step="$item['step']"
                :text="__($item['label'])"
                :icon="$item['icon']"
                class="pt-5"
            >
                <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-base-200 text-primary">
                            <x-ui.icon :name="$panelIcon" class="h-6 w-6" />
                        </div>

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-base-content/60">
                                {{ __('orders_status') }}
                            </p>

                            @if ($panelLabel)
                                <x-ui.badge
                                    :value="__($panelLabel)"
                                    :color="$panelBadgeColor"
                                    soft
                                    class="font-medium"
                                />
                            @endif

                            @if ($panelDescription)
                                <p class="max-w-3xl text-sm leading-6 text-base-content/70">
                                    {{ __($panelDescription) }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </x-mary-step>
        @endforeach
    </x-mary-steps>
</x-ui.card>
