<x-app-layout>
    <x-slot name="title">
        {{ __('Edit :collection', ['collection' => $collection->title]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Edit :collection', ['collection' => $collection->title]) }}
            </h2>
            <div class="flex gap-2">

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

            <x-section submit="{{ route('collections.update', $collection) }}">

                <x-slot name="title">
                    {{ __('Collection Name') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('The collection\'s name and owner information.') }}
                </x-slot>

                <x-slot name="form">

                    @csrf
                    @method('PUT')

                    @include('collection.partials.title')

                </x-slot>
                    
                <x-slot name="actions">
                    <x-button class="">
                        {{ __('Save') }}
                    </x-button>

                    <a class="underline text-sm text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('collections.show', $collection) }}">
                        {{ __('Cancel') }}
                    </a>
                </x-slot>

            </x-section>

        </div>
    </div>
</x-app-layout>
