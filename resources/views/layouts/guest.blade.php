<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Data House') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        <x-analytics::tracking-code />
    </head>
    <body>
        <div class="font-sans text-stone-900 antialiased">
            {{ $slot }}
        </div>

        @livewireScriptConfig
    </body>
</html>
