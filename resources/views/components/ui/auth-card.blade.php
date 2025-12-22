@props([
    'title' => null,
    'subtitle' => null,
    'fullScreen' => false,
    'backgroundClass' => null,
    'maxWidthClass' => 'max-w-md',
])

@php
    $heightClass = $fullScreen ? 'min-h-screen' : 'py-16';
    $bgClass = is_string($backgroundClass) && $backgroundClass !== '' ? $backgroundClass : '';
@endphp

<div {{ $attributes->class("flex items-center justify-center px-4 sm:px-6 lg:px-8 {$heightClass} {$bgClass}") }}>
    <div class="w-full {{ $maxWidthClass }}">
        <x-card class="shadow-xl">
            @if (is_string($title) && $title !== '')
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ $title }}
                    </h2>
                    @if (is_string($subtitle) && $subtitle !== '')
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </x-card>
    </div>
</div>


