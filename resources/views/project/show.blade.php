<x-app-layout>
    <x-slot name="title">
        {{ $project->title }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ $project->title }}
            </h2>
            <div class="flex gap-2">
                <x-add-documents-button :project="$project" />
            </div>
        </div>
        
    </x-slot>

    <div class="bg-white/80 py-3 shadow"  x-data="{ expanded: false }">
        {{-- Project expandable details --}}

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="" x-show="!expanded" x-collapse>
                <div class="grow flex items-center  flex-row sm:gap-6 lg:gap-8">
                    
                    @if ($project->status)
                        <a title="{{ __('Explore :value projects', ['value' => $project->status->name]) }}" href="{{ route('projects.index', ['status' => [$project->status->name]])}}" class="inline px-2 py-1 rounded bg-indigo-100 text-indigo-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800">
                            {{ $project->status->name }}
                        </a>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        @foreach ($project?->countries() as $country)
                            <a title="{{ __('Explore projects in :value', ['value' => $country->name]) }}" href="{{ route('projects.index', ['countries' => [$country->name]])}}" class="inline-flex gap-1 items-center text-xs px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800 group">
                                <x-dynamic-component :component="$country->icon" class="w-4 h-4 text-gray-700 group-hover:text-indigo-600" />
                                {{ $country->name }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex-wrap gap-2 hidden sm:flex">
                        @foreach ($project->topics as $topic)
                            <a title="{{ __('Explore projects in :value', ['value' => $topic]) }}"
                            href="{{ route('projects.index', ['topics' => [$topic]])}}"
                            class="inline-flex gap-1 items-center text-xs px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800 group">
                                <x-heroicon-o-hashtag class="w-3 h-3 text-gray-700 group-hover:text-indigo-600" />
                                {{ $topic }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-8">
                    <div class="grow prose max-w-none line-clamp-1 mt-1">
                        {{ str($project->description)->limit(160)->inlineMarkdown()->toHtmlString() }}
                    </div>
                    <x-small-button class="shrink-0" @click="expanded = ! expanded">{{ __('Expand project details') }}</x-small-button>
                </div>
            </div>

            <div x-cloak x-show="expanded" x-collapse>
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2 items-center">
                            @if ($project->status)
                                <a title="{{ __('Explore :value projects', ['value' => $project->status->name]) }}" href="{{ route('projects.index', ['status' => [$project->status->name]])}}" class="inline px-2 py-1 rounded bg-indigo-100 text-indigo-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800">
                                    {{ $project->status->name }}
                                </a>
                            @endif
                            @if ($project->type)
                                <p class="inline px-2 py-1 rounded bg-lime-100 text-lime-900">
                                    {{ $project->type->name }}
                                </p>
                            @endif
                        </div>

                        <div>
                            <x-small-button @click="expanded = ! expanded">{{ __('Close project details') }}</x-small-button>
                        </div>
                    </div>
                    <p class="text-4xl font-bold max-w-3xl">{{ $project->title }}</p>
                    @if ($project->properties['title_en'] ?? false)
                        <p class="text-xl  max-w-prose">{{ $project->properties['title_en'] }}</p>
                    @endif
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="space-y-4 col-span-2">
                        <div class="prose">
                            {{ str($project->description)->markdown()->toHtmlString() }}
                        </div>
                    </div>
                    <div class="">
                        <div class="space-y-2">
                            
                            @foreach ($topics as $topicName => $selected)
                                <div class="relative group">
                                    <div class="bg-white border border-stone-200 rounded-2xl py-2 px-3 group-hover:border-indigo-600">
                                        <p class="mb-2">
                                            <a href="{{ route('projects.index', ['topics' => '' ])}}" class="group-hover:text-indigo-700">
                                                <span class="z-10 absolute inset-0"></span>
                                                {{ $topicName }}
                                            </a>
                                        </p>
                                        <p class="flex flex-wrap gap-2">
                                            @foreach ($selected as $selectedTopicKey => $selectedTopic)

                                                @if (is_array($selectedTopic))

                                                    <div class="bg-white border border-stone-200 rounded-2xl py-2 px-3 group-hover:border-indigo-600">
                                                        <p class="mb-2">
                                                            <a href="{{ route('projects.index', ['topics' => '' ])}}" class="group-hover:text-indigo-700">
                                                                <span class="z-10 absolute inset-0"></span>
                                                                {{ $selectedTopicKey }}
                                                            </a>
                                                        </p>
                                                        <p class="flex flex-wrap gap-2">
                                                    
                                                            @foreach ($selectedTopic as $subKey => $subValue)
                                                                <a href="{{ route('projects.index', ['topics' => [$subKey]])}}" class="relative z-20 inline-flex gap-2 items-center text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 ">
                                                                    <x-heroicon-o-hashtag class="w-4 h-4" />
                                                                    {{ $subKey }}
                                                                </a>
                                                            @endforeach
                                                        </p>
                                                    </div>
                                                @else
                                                    <a href="{{ route('projects.index', ['topics' => [$selectedTopicKey]])}}" class="relative z-20 inline-flex gap-2 items-center text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 ">
                                                        <x-heroicon-o-hashtag class="w-4 h-4" />
                                                        {{ $selectedTopicKey }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </p>
                                    </div>
                                </div>
                            @endforeach

                            
                        </div>

                        <x-section-border />

                        <p class="text-xs uppercase block text-stone-700 mb-2">{{ __('Countries') }}</p>
                        <div class="mb-6">
                                @foreach ($project->countries() as $country)
                                    <a title="{{ __('Explore projects in :value', ['value' => $country->name]) }}" href="{{ route('projects.index', ['countries' => [$country->name]])}}" class="inline-flex gap-1 items-center text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800 group">
                                        <x-dynamic-component :component="$country->icon" class="w-4 h-4 text-gray-700 group-hover:text-indigo-600" />
                                        {{ $country->name }}
                                    </a>
                                @endforeach
                        </div>
                        <p class="text-xs uppercase block text-stone-700 mb-2">{{ __('Geographic Regions') }}</p>
                        <div class="prose">
                                @foreach ($project->facetRegions() as $region)
                                    <p><a title="{{ __('Explore projects in :value', ['value' => $region]) }}" href="{{ route('projects.index', ['region' => [$region]])}}">{{ $region }}</a></p>
                                @endforeach
                        </div>
                        
                        @if ($project->links )
                            <x-section-border />

                            <p class="text-xs uppercase block text-stone-700 mb-2">{{ __('Links') }}</p>
                            <div class="prose">
                                @foreach ($project->links ?? [] as $link)
                                    <p><a href="{{ $link['url'] }}" target="_blank">{{ $link['text'] }}</a></p>
                                @endforeach
                            </div>
                        @endif
                        
                        <x-section-border />

                        <p class="text-xs uppercase block text-stone-700 mb-2">{{ __('Signature') }}</p>
                        <div class="prose">
                            <code>{{ $project->slug }}</code>
                        </div>
                
                        @feature(Flag::showProjectFunding())
                        <div class="h-4"></div>
                        <p class="text-xs uppercase block text-stone-700">{{ __('Funding') }}</p>
                        <div class="prose">
                
                            @if ($project->funding['iki'] ?? false)
                                <x-currency :value="$project->funding['iki']" />
                            @else
                                <p>{{ __('Currently not available') }}</p>
                            @endif
                
                        </div>
                        @endfeature
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            

            

            <x-search-form
                action=""
                :clear="route('projects.show', $project)"
                :search-query="$searchQuery ?? null"
                :search-placeholder="__('Search within the project...')"
                :applied-filters-count="$applied_filters_count"
                >

                <x-slot:filters>
                    @include('document.partials.filters')
                </x-slot>

            </x-search-form>

            <div class="flex space-x-4 mt-3 divide-x divide-stone-200 items-center justify-end">
                @if ($is_search)
                    <div class="text-sm py-2 text-right">{{ trans_choice(':total document found|:total documents found', $documents->total(), ['total' => $documents->total()]) }}</div>
                @endif
    
                @if (!$is_search)
                    <div class="text-sm py-2 text-right">{{ trans_choice(':total document in the project|:total documents in the project', $documents->total(), ['total' => $documents->total()]) }}</div>
                @endif

                <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />
            </div>

            @php
                $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
            @endphp

            <x-dynamic-component :component="$visualizationStyle" class="mt-6" :documents="$documents" empty="{{ $is_search ? __('No documents matching the search criteria.') : __('No documents available for the project.') }}" />
            
            <div class="mt-2">{{ $documents?->links() }}</div>
            
        </div>
    </div>

</x-app-layout>
