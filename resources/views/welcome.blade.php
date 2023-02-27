<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="text-3xl font-bold text-center">{{ config('app.name') }}</h1>
    </x-authentication-card>
</x-guest-layout>