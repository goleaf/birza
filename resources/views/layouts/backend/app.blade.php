<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @wireUiStyles
    @wireUiScripts
    @stack('head-scripts')
</head>
<body
    x-data
    class="min-h-screen bg-base-200 text-base-content"
    style="--admin-primary: {{ $adminThemeColors['primary'] ?? '#13261F' }}; --admin-accent: {{ $adminThemeColors['accent'] ?? '#D2FF72' }}; --admin-surface: {{ $adminThemeColors['surface'] ?? '#F4C16D' }};"
>
    <div class="flex min-h-screen flex-col">
        <div
            class="h-1 w-full"
            style="background: linear-gradient(90deg, var(--admin-primary), var(--admin-accent), var(--admin-surface));"
        ></div>

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
    @auth('admin')
        <x-mary-spotlight
            :search-text="__('backend_spotlight_search_text')"
            :no-results-text="__('backend_spotlight_no_results')"
        />
    @endauth

    @livewireScripts
    @stack('body-scripts')
</body>
</html>
