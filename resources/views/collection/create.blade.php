<x-app-layout>
    <x-slot name="title">
        {{ __('Create collection') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Create a collection') }}
            </h2>
            <div class="flex gap-2">
                
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            
            <x-section submit="{{ route('collections.store') }}">
                <x-slot name="title">
                    {{ __('Collection Name') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('The collection\'s name and owner information.') }}
                </x-slot>

                <x-slot name="form">
                    @csrf

                    @include('collection.partials.title')

                </x-slot>

                <x-slot name="actions">
                    <x-button class="">
                        {{ __('Create') }}
                    </x-button>
                </x-slot>
            </x-section>
        </div>
    </div>
</x-app-layout>
