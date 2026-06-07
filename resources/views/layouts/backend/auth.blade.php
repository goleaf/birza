<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @wireUiStyles
    @stack('head-scripts')
</head>
<body
    class="min-h-screen bg-base-200 text-base-content antialiased"
    style="--admin-primary: {{ $adminThemeColors['primary'] ?? '#13261F' }}; --admin-accent: {{ $adminThemeColors['accent'] ?? '#D2FF72' }}; --admin-surface: {{ $adminThemeColors['surface'] ?? '#F4C16D' }};"
>
    <x-mary-main full-width>
        <x-slot:content class="min-h-screen p-0">
            <div
                class="min-h-screen"
                style="background:
                    radial-gradient(circle at top, color-mix(in srgb, var(--admin-primary) 14%, transparent), transparent 45%),
                    linear-gradient(180deg, color-mix(in srgb, var(--admin-surface) 12%, #f8f6f2), color-mix(in srgb, var(--admin-accent) 10%, #efe9dd));"
            >
                <div class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </div>
        </x-slot:content>
    </x-mary-main>

    @wireUiScripts
    @livewireScripts
    @stack('body-scripts')
</body>
</html>
