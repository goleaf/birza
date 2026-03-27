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
    @wireUiScripts
    @stack('head-scripts')
</head>
<body class="min-h-screen bg-base-200 text-base-content">
    <div class="flex min-h-screen flex-col">
        <header>
            @include('layouts.backend.navigation')
        </header>

        <!-- Page Content -->
        <main class="flex-grow">
            <div class="w-full px-4 pb-10 pt-6">
                <x-ui.flash-messages class="mb-4" />

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>

        @auth('admin')
            <footer>
                @include('layouts.backend.footer')
            </footer>
        @endauth
    </div>

    <x-notifications z-index="z-50" position="top-end" />
    <x-dialog z-index="z-50" blur="md" align="center" />

    @livewireScripts
    @stack('body-scripts')
</body>
</html>
