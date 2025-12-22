<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no">
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
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <header>
            @include('layouts.backend.navigation')
        </header>

        <!-- Page Content -->
        <main class="flex-grow py-12">
            <div class="w-full px-4">
                <x-ui.flash-messages class="max-w-7xl mx-auto mt-4" />

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

    @livewireScripts
    @stack('body-scripts')
</body>
</html>
