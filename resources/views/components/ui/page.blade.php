@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class('space-y-6') }}>
    @if (is_string($title) && $title !== '')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $title }}
                </h1>
                @if (is_string($description) && $description !== '')
                    <p class="mt-1 text-sm text-gray-500">
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


