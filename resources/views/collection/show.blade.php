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

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="space-y-2">
                <div class="flex justify-between mt-2 relative">
                    <div class="" x-data="{ open: false }" x-trap="open" @click.away="open = false" @close.stop="open = false">
                        <button type="button" @click="open = ! open" class="rounded px-2 py-1 text-sm text-lime-700 flex items-center gap-1 border border-transparent hover:bg-lime-100 hover:border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 focus:bg-lime-100 focus:border-lime-500">
                            <x-heroicon-s-sparkles class="text-lime-500 h-6 w-6" />
                            {{ __('Ask a question to all documents in this collection...') }}
                        </button>

                        <div x-cloak
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute bg-lime-50 z-50 mt-2 w-full rounded-md shadow-lg shadow-lime-800/20 left-0"
                            style="display: none;">
                            <div class="rounded-md ring-1 ring-lime-300 ring-opacity-20 min-h-[10rem] p-4">

                                <livewire:multiple-question-input :collection="$collection" :strategy="\App\Models\CollectionStrategy::STATIC" />
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3">
                    @foreach ($questions as $question)
                        <x-question-card :question="$question" />
                    @endforeach
                </div>
            </div>


            <x-document-grid class="mt-6" :documents="$documents" empty="{{ __('Collection is empty') }}" />
            
            <div class="mb-4">
                @if ($collection->draft)
                    <span class="inline-block text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">{{ __('pending review') }}</span>
                @endif
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
