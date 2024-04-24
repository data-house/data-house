<x-app-layout>
    <x-slot name="title">
        {{ __('Project database') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Project database')">

            <x-slot:actions>

            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                

            <x-search-form
                action=""
                :clear="route('projects.index')"
                :search-query="$searchQuery ?? null"
                :search-placeholder="__('Search project database...')"
                :applied-filters-count="$applied_filters_count"
                >

                <x-slot:filters>

                    <div class="grid auto-rows-min grid-cols-1 md:col-span-4 gap-y-10 md:grid-cols-2 md:gap-x-6">

                        @feature(Flag::typeProjectFilter())
                            <fieldset>
                            <legend class="block font-medium">{{ __('Scope') }}</legend>
                            <div class="flex  pt-6 gap-4 md:gap-6">
                                @foreach ($facets['type'] as $item)
                                    <div class="flex items-center text-base sm:text-sm">
                                    <input id="type-{{ $item->name }}" name="type[]" value="{{ $item->name }}" @checked(in_array($item->name, $filters['type'] ?? [])) type="checkbox" class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="type-{{ $item->name }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item->label() }}</label>
                                    </div>
                                @endforeach
                            </div>
                            </fieldset>
                        @endfeature

                        <fieldset>
                            <legend class="block font-medium">{{ __('Status') }}</legend>
                            <div class="flex  pt-6 gap-4 md:gap-6">
                            @foreach ($facets['status'] as $status)
                                <div class="flex items-center text-base sm:text-sm">
                                <input id="status-{{ $status->name }}" name="status[]" value="{{ $status->name }}" type="checkbox" @checked(in_array($status->name, $filters['status'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="status-{{ $status->name }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $status->label() }}</label>
                                </div>
                            @endforeach
                            </div>
                        </fieldset>
                    </div>

                    {{-- <fieldset>
                        <legend class="block font-medium">{{ __('Area') }}</legend>
                        <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                        @foreach ($facets['topic'] as $topicKey => $topic)
                            <div class="flex items-center text-base sm:text-sm">
                            <input id="topic-{{ $topicKey }}" name="topics[]" value="{{ $topicKey }}" type="checkbox" @checked(in_array($topicKey, $filters['topics'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="topic-{{ $topicKey }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $topic }}</label>
                            </div>
                        @endforeach
                        </div>
                    </fieldset> --}}
                    
                    @foreach ($topics as $scheme => $concepts)
                        <fieldset>
                            <legend class="block font-medium">{{ $scheme }}</legend>
                            <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                            @foreach ($concepts as $concept)

                                <div class="flex items-center text-base sm:text-sm">
                                <input id="topic-{{ $concept['id'] ?? str($concept['name'])->slug()->toString() }}" name="topics[]" value="{{ $concept['id'] ?? str($concept['name'])->slug()->toString() }}" type="checkbox" @checked(in_array($concept['id'] ?? str($concept['name'])->slug()->toString(), $filters['topics'] ?? []) || in_array($concept['name'], $filters['topics'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="topic-{{ $concept['id'] ?? str($concept['name'])->slug()->toString() }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $concept['name'] }}</label>
                                </div>
                            @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                    
                    <fieldset>
                        <legend class="block font-medium">{{ __('Country') }}</legend>
                        <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                        @foreach ($facets['countries'] as $item)
                            <div class="flex items-center text-base sm:text-sm">
                            <input id="countries-{{ str($item)->slug()->toString() }}" name="countries[]" value="{{ $item }}" type="checkbox" @checked(in_array($item, $filters['countries'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="countries-{{ str($item)->slug()->toString() }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item }}</label>
                            </div>
                        @endforeach
                        
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="block font-medium">{{ __('Region') }}</legend>
                        <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
                        @foreach ($facets['regions'] as $item)
                            <div class="flex items-center text-base sm:text-sm">
                            <input id="region-{{ $item }}" name="region[]" value="{{ $item }}" type="checkbox" @checked(in_array($item, $filters['region'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="region-{{ $item }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item }}</label>
                            </div>
                        @endforeach
                        </div>
                    </fieldset>
                </x-slot>

            </x-search-form>

            <div class="flex space-x-4 mt-3 divide-x divide-stone-200 items-center justify-end">

                @if ($is_search && $projects->isNotEmpty())
                    <div class="text-sm py-2 text-right">{{ trans_choice(':total project found|:total projects found', $projects->total(), ['total' => $projects->total()]) }}</div>
                @endif

                @if (!$is_search && $projects->isNotEmpty())
                    <div class="text-sm py-2 text-right">{{ trans_choice(':total project|:total projects', $projects->total(), ['total' => $projects->total()]) }}</div>
                @endif

                <div class="pl-4">
                    <x-sorting-dropdown model="\App\Models\Project" />
                </div>

            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($projects as $project)
                    <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
                        
                        @feature(Flag::typeProjectFilter())
                            <div class="flex justify-between">
                                @if ($project->type)
                                    <p class="inline text-xs px-2 py-1 rounded bg-lime-100 text-lime-900">
                                        {{ $project->type->label() }}
                                    </p>
                                @endif
                            </div>
                        @endfeature

                        <a href="{{ route('projects.show', $project) }}" class="block font-bold group-hover:text-blue-800">
                            <span class="z-10 absolute inset-0"></span>{{ $project->title }}
                        </a>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($project->formattedTopics()->pluck('selected')->collapse() as $topic)
                                <span class="inline-flex gap-2 items-center text-xs px-2 py-1 rounded-xl bg-stone-100 text-stone-900">
                                    <x-heroicon-o-hashtag class="w-4 h-4" />
                                    {{ $topic['name'] }}
                                </span>
                            @endforeach
                        </div>

                        <div class="space-x-1 text-sm">
                            <span>{{ $project->countries()->pluck('name')->join(', ') }}</span>
                            <span>/</span>
                            <span>{{ $project->facetRegions()->join(', ') }}</span>
                        </div>

                    </div>
                @empty
                    <div class="col-span-3">
                        @if ($is_search)
                            <p>{{ __('No projects matching the search criteria.') }}</p>
                        @else
                            <p>{{ __('No projects fetched from the project database.') }}</p>
                        @endif
                    </div>
                @endforelse
            
            </div>
            

            <div class="mt-2">{{ $projects?->links() }}</div>
        </div>
    </div>
</x-app-layout>
