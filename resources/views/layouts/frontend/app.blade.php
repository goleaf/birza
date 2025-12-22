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
    <div class="min-h-screen bg-gray-100">

        <header>
            @include('layouts.frontend.header')
        </header>

        <main class="py-12">
            @if (isset($fullWidth) && $fullWidth)
                <div class="w-full px-4 sm:px-6 lg:px-8">
                    <x-ui.flash-messages class="mb-6" />
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            @else
                <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <x-ui.flash-messages class="mb-6" />
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            @endif
        </main>

        {{--
        <footer>
            @include('layouts.frontend.footer')
        </footer>
    --}}
    </div>

    <x-notifications z-index="z-50" position="top-end" />
    <x-dialog z-index="z-50" blur="md" align="center" />

    @livewireScripts
    @stack('body-scripts')


    
</body>

</html>
