<x-app-layout>
    <x-slot name="title">
        {{ __('Categories') }} - {{ __('Admin Area') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Categories')">

            <x-slot:actions>
                @include('admin.navigation')
            </x-slot>
        </x-page-heading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <livewire:concepts.concept-collection-listing-component />
           
        </div>
    </div>
</x-app-layout>
