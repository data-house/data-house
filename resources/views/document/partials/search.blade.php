<x-search-form
    action=""
    :clear="route('documents.library')"
    :search-query="$searchQuery ?? null"
    :search-placeholder="__('Search within the digital library...')"
    :applied-filters-count="$applied_filters_count"
    >

    <x-slot:filters>
        @if (!empty($sorting))
            <input type="hidden" name="sort" value="{{ $sorting }}">
        @endif

        @include('document.partials.filters')
    </x-slot>

    

    <div class="flex justify-between mt-2 relative">
        <div>
            @feature(Flag::questionWholeLibraryWithAI())
                <div class="" x-data="{ open: false }" x-trap="open" @click.away="open = false" @close.stop="open = false">
                    <button type="button" @click="open = ! open" class="rounded px-2 py-1 text-sm text-lime-700 flex items-center gap-1 border border-transparent hover:bg-lime-100 hover:border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 focus:bg-lime-100 focus:border-lime-500">
                        <x-heroicon-s-sparkles class="text-lime-500 h-6 w-6" />
                        @if ($searchQuery)
                        {{ __('Ask a question to all documents found...') }}
                        <span class="inline-block text-xs rounded-full px-2 py-0.5 bg-stone-200 text-stone-600">
                            {{ __('coming soon') }}
                        </span>
                        @else
                        {{ __('Ask a question to all documents in the library...') }}
                        @endif
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
                            
                            @unless ($searchQuery)
                            <livewire:multiple-question-input :strategy="\App\Models\CollectionStrategy::LIBRARY" />
                            @endunless
                            
                        </div>
                    </div>
                </div>
            @endfeature
        </div>

        @feature(Flag::collections())
        @can('create', \App\Models\Collection::class)
            @if ($searchQuery)
            <p>
                <x-button class="text-xs">{{ __('Save search as collection') }}&nbsp;
                    <span class="inline-block text-xs normal-case rounded-full px-2 py-0.5 bg-stone-200 text-stone-600">
                        {{ __('coming soon') }}
                    </span>
                </x-button>
            </p>
            @endif
        @endcan
        @endfeature
    </div>

</x-search-form>