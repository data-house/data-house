<x-app-layout>
    <x-slot name="title">
        {{ __('Project database') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Project database')">

            <x-slot:actions>

            </x-slot>

            {{-- @include('library-navigation-menu') --}}
        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <form  action="" method="get" x-data="{showFilters: false}" @click.away="showFilters = false" @close.stop="showFilters = false">
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
                        <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ __('Search project database...') }}" />
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
                            @foreach ($facets['topic'] as $item)
                                <div class="flex items-center text-base sm:text-sm">
                                <input id="topic-{{ $item }}" name="topic[]" value="{{ $item }}" type="checkbox" @checked(in_array($item, $filters['topic'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="topic-{{ $item }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item }}</label>
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
                                <input id="countries-{{ $item->name }}" name="countries[]" value="{{ $item->value }}" type="checkbox" @checked(in_array($item->value, $filters['countries'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
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
                                <input id="region-{{ $item }}" name="region[]" value="{{ $item }}" type="checkbox" @checked(in_array($item, $filters['region'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
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

            <div class="mt-6 grid grid-cols-3 gap-4">
                @forelse ($projects as $project)
                    <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
            
                        <div class="flex justify-between">
                            <p class="inline text-xs px-2 py-1 rounded bg-lime-100 text-lime-900">
                                {{ $project->type->name }}
                            </p>
                            <p class="inline text-xs px-2 py-1 rounded bg-stone-100 text-stone-900 max-w-[10rem] truncate group-hover:max-w-none">
                                {{ $project->slug }}
                            </p>
                        </div>

                        <a href="{{ route('projects.show', $project) }}" class="block font-bold group-hover:text-blue-800">
                            <span class="z-10 absolute inset-0"></span>{{ $project->title }}
                        </a>

                        @foreach ($project->topics as $topic)
                            <span class="flex gap-2 items-center text-xs px-2 py-1 rounded-xl bg-stone-100 text-stone-900">
                                <x-heroicon-o-hashtag class="w-4 h-4" />
                                {{ $topic }}
                            </span>
                        @endforeach

                        <div class="space-x-1 text-sm">
                            <span>{{ $project->countries()->pluck('value')->join(', ') }}</span>
                            <span>/</span>
                            <span>{{ $project->regions()->join(', ') }}</span>
                        </div>

                    </div>
                @empty
                    <div class="col-span-3">
                        <p>{{ __('No projects fetched from the project database.') }}</p>
                    </div>
                @endforelse
            
            </div>
            

            <div class="mt-2">{{ $projects?->links() }}</div>
        </div>
    </div>
</x-app-layout>
