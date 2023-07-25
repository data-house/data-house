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

                @can('create', \App\Model\Document::class)
                    <x-button-link href="{{ route('documents.create') }}">
                        {{ __('Upload Document') }}
                    </x-button-link>
                @endcan
                @can('viewAny', \App\Model\Import::class)
                    <x-button-link href="{{ route('imports.index') }}">
                        {{ __('Import Documents') }}
                    </x-button-link>
                @endcan
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
                                <span class="inline-block text-xs rounded-full px-2 py-0.5 bg-stone-200">
                                    {{ __('cooming soon') }}
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
                        <x-button class="text-xs">Save search as collection</x-button>
                    </p>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4">
                @forelse ($documents as $document)
                    <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
                        <div class="aspect-video bg-white -mx-4 -mt-4 flex items-center justify-center">
                            {{-- Space for the thumbnail --}}
                            <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
                        </div>

                        <a href="{{ route('documents.show', $document) }}" class="block font-bold truncate group-hover:text-blue-800">
                            <span class="z-10 absolute inset-0"></span>{{ $document->title }}
                        </a>
                        <p>{{ $document->created_at }}</p>
                        <p>
                            @if ($document->draft)
                                <span class="inline-block text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">{{ __('pending review') }}</span>
                            @endif
                        </p>
                    </div>
                @empty
                    <div class="col-span-3">
                        <p>{{ __('No documents in the library.') }}</p>
                    </div>
                @endforelse

            </div>
            <div class="mt-2">{{ $documents?->links() }}</div>
        </div>
    </div>
</x-app-layout>
