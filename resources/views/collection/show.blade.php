<x-app-layout>
    <x-slot name="title">
        {{ $collection->title }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ $collection->title }}
            </h2>
            <div class="flex gap-2">

                @can('viewAny', \App\Models\Collection::class)

                    <livewire:collection-switcher />

                @endcan
                
                @can('update', $collection)
                    <x-button-link href="{{ route('collections.edit', $collection) }}">
                        {{ __('Edit') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-document-grid :documents="$collection->documents" empty="{{ __('Collection is empty') }}" />
            
            <div class="mb-4">
                @if ($collection->draft)
                    <span class="inline-block text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">{{ __('pending review') }}</span>
                @endif
            </div>
            <div class="grid md:grid-cols-3">

                
            </div>
        </div>
    </div>

    @can('viewAny', \App\Models\Question::class)
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- TODO: shows questions related to this collection --}}

                {{-- <x-collection-chat :collection="$collection" /> --}}
            </div>
        </div>
    @endcan
</x-app-layout>
