@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class('space-y-6') }}>
    @if (is_string($title) && $title !== '')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-semibold tracking-tight">
                    {{ $title }}
                </h1>
                @if (is_string($description) && $description !== '')
                    <p class="text-sm text-base-content/70">
                        {{ $description }}
                    </p>
                @endif
            </div>

            @isset($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>

