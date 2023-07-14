<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="text-3xl font-bold text-center">{{ config('app.name') }}</h1>

        <div class="mt-8 text-center">
            <x-button-link href="{{ route('login') }}">
                {{ __('Log in') }}
            </x-button-link>
        </div>
    </x-authentication-card>
</x-guest-layout>