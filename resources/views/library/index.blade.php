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
                <form action="" method="get" x-data="{showFilters: false}" @click.away="showFilters = false" @close.stop="showFilters = false">
                    <div class="flex space-x-6 divide-x divide-stone-200 items-center">
                        <div>
                            <button type="button"  @click="showFilters = ! showFilters" class="group flex items-center font-medium text-stone-700" aria-controls="disclosure-1" aria-expanded="false">
                                @if ($applied_filters_count > 0)
                                    <x-heroicon-s-funnel aria-hidden="true" class="mr-2 h-5 w-5 flex-none text-stone-400 group-hover:text-stone-500" />
                                @else
                                    <x-heroicon-o-funnel aria-hidden="true" class="mr-2 h-5 w-5 flex-none text-stone-400 group-hover:text-stone-500" />
                                @endif
                                
                                {{ trans_choice('{0} Filters|{1} :num Filter|[2,*] :num Filters', $applied_filters_count, ['num' => $applied_filters_count])}}
                            </button>
                        </div>
                        <div class="pl-6 flex-grow">
                            <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ __('Search within the digital library...') }}" />
                        </div>
                    </div>


                    <div class="border-b border-gray-200 py-10" id="disclosure-1" x-cloak x-show="showFilters">
                        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-x-4 px-4 text-sm sm:px-6 md:gap-x-6 lg:px-8">
                          <div class="grid auto-rows-min grid-cols-1 gap-y-10 md:grid-cols-2 md:gap-x-6">
                            <fieldset>
                              <legend class="block font-medium">{{ __('Type') }}</legend>
                              <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                                @foreach ($facets['type'] as $item)
                                    <div class="flex items-center text-base sm:text-sm">
                                    <input id="type-{{ $item->name }}" name="type[]" value="{{ $item->name }}" @checked(in_array($item->name, $filters['type'] ?? [])) type="checkbox" class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="type-{{ $item->name }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item->name }}</label>
                                    </div>
                                @endforeach
                              </div>
                            </fieldset>
                            <fieldset>
                              <legend class="block font-medium">{{ __('Topic') }}</legend>
                              <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                                @foreach ($facets['topic'] as $topicKey => $topic)
                                <div class="flex items-center text-base sm:text-sm">
                                <input id="topic-{{ $topicKey }}" name="project_topics[]" value="{{ $topicKey }}" type="checkbox" @checked(in_array($topicKey, $filters['project_topics'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="topic-{{ $topicKey }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $topic }}</label>
                                </div>
                            @endforeach
                              </div>
                            </fieldset>
                          </div>
                          <div class="grid auto-rows-min grid-cols-1 gap-y-10 md:grid-cols-2 md:gap-x-6">
                            <fieldset>
                              <legend class="block font-medium">{{ __('Country') }}</legend>
                              <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                                @foreach ($facets['countries'] as $item)
                                    <div class="flex items-center text-base sm:text-sm">
                                    <input id="countries-{{ $item->name }}" name="project_countries[]" value="{{ $item->value }}" type="checkbox" @checked(in_array($item->value, $filters['project_countries'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="countries-{{ $item->name }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item->value }}</label>
                                    </div>
                                @endforeach
                                
                              </div>
                            </fieldset>
                            <fieldset>
                              <legend class="block font-medium">{{ __('Region') }}</legend>
                              <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                                @foreach ($facets['regions'] as $item)
                                    <div class="flex items-center text-base sm:text-sm">
                                    <input id="region-{{ $item }}" name="project_region[]" value="{{ $item }}" type="checkbox" @checked(in_array($item, $filters['project_region'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="region-{{ $item }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item }}</label>
                                    </div>
                                @endforeach
                              </div>
                            </fieldset>
                          </div>
                        </div>
    
                        <p class="text-right mt-2">
                            <x-button type="submit">{{ __('Apply and search') }}</x-button>
                        </p>
                      </div>
                </form>
                <div class="flex justify-between mt-2 relative">
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

            @if ($is_search && $documents->isNotEmpty())
                <div class="text-sm mt-3 py-2 text-right">{{ trans_choice(':total document found|:total documents found', $documents->total(), ['total' => $documents->total()]) }}</div>
            @endif

            @if (!$is_search && $documents->isNotEmpty())
                <div class="text-sm mt-3 py-2 text-right">{{ trans_choice(':total document in the library|:total documents in the library', $documents->total(), ['total' => $documents->total()]) }}</div>
            @endif

            <x-document-grid class="mt-3" :documents="$documents" empty="{{ $is_search ? __('No documents matching the search criteria.') :__('No documents in the library') }}" />

            <div class="mt-2">{{ $documents?->links() }}</div>
        </div>
    </div>
</x-app-layout>
