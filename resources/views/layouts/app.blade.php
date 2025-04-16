<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) && !empty($title) ? $title .' - ' : ''}}{{ config('app.name', 'Data House') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <x-analytics::tracking-code />
    </head>
    <body class="font-sans antialiased">
        <x-banner />
        

        <div class="min-h-screen bg-stone-100">
            @livewire('navigation-menu')

            <x-update-expired-password-banner />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScriptConfig

        @livewire('livewire-ui-slideover')
    </body>
</html>
