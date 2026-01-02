@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class('space-y-4') }}>
    @if (is_string($title) && $title !== '')
        <div>
            <h2 class="text-lg font-semibold">{{ $title }}</h2>
            @if (is_string($description) && $description !== '')
                <p class="mt-1 text-sm text-base-content/70">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
