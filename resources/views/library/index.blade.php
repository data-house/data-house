<x-app-layout>
    <x-slot name="title">
        {{ __('Digital Library') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Digital Library')">

            <x-slot:actions>
                @can('viewAny', \App\Models\Collection::class)

                    <livewire:collection-switcher />

                @endcan

                @if (Auth::user()->can('create', \App\Model\Document::class) || Auth::user()->can('viewAny', \App\Model\Import::class))

                    <x-dropdown align="right">
                        <x-slot name="trigger">
                            <x-button type="button" class="justify-self-end inline-flex gap-1 items-center">
                                {{ __('Add documents') }}
                            </x-button>
                        </x-slot>
                    
                        <x-slot name="content">

                            @can('create', \App\Model\Document::class)
                                <x-dropdown-link 
                                    href="{{ route('documents.create') }}"
                                    :active="request()->routeIs('documents.create')"
                                    >
                                    {{ __('Upload Document') }}
                                </x-dropdown-link>
                            @endcan
                            @can('viewAny', \App\Model\Import::class)
                                <x-dropdown-link 
                                    href="{{ route('imports.index') }}"
                                    :active="request()->routeIs('imports.*')"
                                    >
                                    {{ __('Import Documents') }}
                                </x-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-dropdown>
                @endif
            </x-slot>

            @include('library-navigation-menu')
        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div>
                <form action="" method="get">
                    <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ __('Search within the digital library...') }}" />
                </form>
                <div class="flex justify-between mt-2 relative">
                    <div class="" x-data="{ open: false }" x-trap="open" @click.away="open = false" @close.stop="open = false">
                        <button @click="open = ! open" class="rounded px-2 py-1 text-sm text-lime-700 flex items-center gap-1 border border-transparent hover:bg-lime-100 hover:border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 focus:bg-lime-100 focus:border-lime-500">
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

                    @if ($searchQuery)
                    <p>
                        <x-button class="text-xs">{{ __('Save search as collection') }}&nbsp;
                            <span class="inline-block text-xs normal-case rounded-full px-2 py-0.5 bg-stone-200 text-stone-600">
                                {{ __('coming soon') }}
                            </span>
                        </x-button>
                    </p>
                    @endif
                </div>
            </div>

            <x-document-grid class="mt-6" :documents="$documents" empty="{{ __('No documents in the library') }}" />

            <div class="mt-2">{{ $documents?->links() }}</div>
        </div>
    </div>
</x-app-layout>
