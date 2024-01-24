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

            </div>
        </div>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 space-y-2">
                <div class="flex gap-2">
                    <p class="inline px-2 py-1 rounded bg-white text-stone-900">
                        {{ $project->slug }}
                    </p>
                    @if ($project->status)
                        <p class="inline px-2 py-1 rounded bg-indigo-100 text-indigo-900">
                            {{ $project->status->name }}
                        </p>
                    @endif
                    @if ($project->type)
                        <p class="inline px-2 py-1 rounded bg-lime-100 text-lime-900">
                            {{ $project->type->name }}
                        </p>
                    @endif
                </div>

                <p class="text-4xl font-bold max-w-3xl">{{ $project->title }}</p>
                @if ($project->properties['title_en'] ?? false)
                    <p class="text-xl  max-w-prose">{{ $project->properties['title_en'] }}</p>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4">

                <div class="space-y-4 col-span-2">
                    <p class="text-xs uppercase block text-stone-700">{{ __('Description') }}</p>
                    <div class="prose">
                        {{ str($project->description)->markdown()->toHtmlString() }}
                    </div>
                </div>
                <div class="space-y-4">
                    <p class="text-xs uppercase block text-stone-700">{{ __('Topics') }}</p>

                    <div class="space-y-2">
                        @foreach ($topics as $topic)
                            <div class="relative group">
                                <div class="bg-white border border-stone-200 rounded-2xl py-2 px-3 group-hover:border-indigo-600">
                                    <p class="mb-2">
                                        <a href="{{ route('projects.index', ['topics' => collect($topic['children'])->pluck('name')->toArray() ])}}" class="group-hover:text-indigo-700">
                                            <span class="z-10 absolute inset-0"></span>
                                            {{ $topic['name'] }}
                                        </a>
                                    </p>

                                    <p class="flex flex-wrap gap-2">
                                        @foreach ($topic['selected'] as $selectedTopic)
                                            <a href="{{ route('projects.index', ['topics' => [$selectedTopic['name']]])}}" class="relative z-20 inline-flex gap-2 items-center text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 ">
                                                <x-heroicon-o-hashtag class="w-4 h-4" />
                                                {{ $selectedTopic['name'] }}
                                            </a>
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                <div>
                    <p class="text-xs uppercase block text-stone-700">{{ __('Countries') }}</p>
                    <div class="prose">
                        <ul>
                            @foreach ($project->countries()->pluck('value') as $country)
                                <li>{{ $country }}</li>
                            @endforeach 
                        </ul>
                    </div>
                </div>
                <div>
                    <p class="text-xs uppercase block text-stone-700">{{ __('Regions') }}</p>
                    <div class="prose">
                        <ul>
                            @foreach ($project->regions() as $region)
                                @php
                                    $chunks = $region->split(3);                                
                                @endphp
                                <li>
                                    {{ $chunks[0]->first() }}
                                    @if ($chunks[1] ?? null)
                                        <ul>
                                            <li>
                                                {{ $chunks[1]->first() }}
                                                @if ($chunks[2] ?? null)
                                                <ul>
                                                    <li>{{ $chunks[2]->first() }}</li>
                                                </ul>
                                                @endif
                                            </li>
                                        </ul>
                                        
                                    @endif
                                </li>
                            @endforeach 
                        </ul>
                    </div>
                </div>
                <div>
                    <p class="text-xs uppercase block text-stone-700">{{ __('Funding') }}</p>
                    <div class="prose">
                        
                        @if ($project->funding['iki'] ?? false)
                            <x-currency :value="$project->funding['iki']" />
                        @else
                            <p>{{ __('Currently not available') }}</p>
                        @endif
                        
                    </div>
                </div>

            </div>

            <div class="h-10"></div>

            <div class="max-w-7xl mx-auto mb-2 flex justify-between items-center">
                <h3 class="text-lg font-semibold">{{ __('Project reports and documents') }}</h3>

                <div class="flex items-center gap-2">
                        
                    <x-add-documents-button />

                    <div class="divide-x"></div>

                    <x-visualization-style-switcher :user="auth()->user()" />
                </div>
            </div>

            @php
                $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
            @endphp

            <x-dynamic-component :component="$visualizationStyle" class="mt-6" :documents="$project->documents" empty="{{ __('No documents available for the project.') }}" />
            
            
        </div>
    </div>

</x-app-layout>
