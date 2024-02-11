<x-app-layout>
    <x-slot name="title">
        {{ $document->title }}
    </x-slot>
    <x-slot name="header">
        <div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight space-y-2 sm:space-y-0 sm:flex sm:gap-4 md:items-center">
                {{ $document->title }}

                @feature(Flag::editDocumentVisibility())
                <x-document-visibility-badge class="ml-4" :value="$document->visibility" />
                @endfeature
            </h2>
            <div class="flex gap-2">
                @can('view', $document)
                    <x-button-link href="{{ $document->viewerUrl() }}" target="_blank">
                        {{ __('Open Document') }}
                    </x-button-link>
                @endcan
                @can('update', $document)
                    <x-button-link href="{{ route('documents.edit', $document) }}">
                        {{ __('Edit') }}
                    </x-button-link>

                    @feature(Flag::editDocumentVisibility())
                    <livewire:document-visibility-selector :document="$document" />
                    @endfeature
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($hasActivePipelines)
                <div class="mb-4 bg-yellow-100 text-yellow-900 flex items-center gap-2 px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                      </svg>
                      
                    <span class="">{{ __('Document under processing. Preview and search might not be available while the document is being processed.') }}</span>
                </div>
            @endif

            <div class="grid md:grid-cols-3 gap-3">

                <div class="col-span-2">

                    <div class="max-h-60 overflow-y-auto">
                        @if ($document->description)
                            <div class="prose prose-green">
                                {!! \Illuminate\Support\Str::markdown($document->description) !!}
                            </div>
                        @else
                            <div class="prose prose-green">
                                {!! \Illuminate\Support\Str::markdown(__('This document doesn\'t have an abstract. [Be the first one to contribute](:url).', ['url' => route('documents.edit', $document)])) !!}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- <div class="space-y-4"> --}}
                    <div class="aspect-video bg-white flex items-center justify-center">
                        {{-- Space for the thumbnail --}}
                        <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-bold">{{ __('Collections') }}</h4>
                        
                        <livewire:document-collections :document="$document" />
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-bold">{{ __('Project') }}</h4>
                        
                        @if ($document->project)
                            @can('view', $document->project)    
                                <div class="space-y-1">
                                    <p>
                                        <a class="border-b pb-0.5 hover:border-blue-600 hover:text-blue-800 focus:text-blue-800" href="{{ route('projects.show', $document->project) }}">{{ $document->project?->title }}</a>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase block text-stone-700">{{ __('Topics') }}</p>

                                    <p class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($document->project->topics as $topic)
                                            <a title="{{ __('Explore documents for projects in :value', ['value' => $topic]) }}" href="{{ route('documents.library', ['project_topics' => [$topic]])}}" class="relative z-20 inline-flex gap-2 items-center text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 ">
                                                <x-heroicon-o-hashtag class="w-4 h-4" />
                                                {{ $topic }}
                                            </a>
                                        @endforeach
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase block text-stone-700">{{ __('Countries') }}</p>
                                    <div class="prose">
                                        @foreach ($document->project?->countries()->pluck('value') as $country)
                                            <p><a title="{{ __('Explore documents for projects in :value', ['value' => $country]) }}" href="{{ route('documents.library', ['project_countries' => [$country]])}}">{{ $country }}</a></p>
                                        @endforeach 
                                    </div>
                                </div>
                            @else
                                <p class="prose">{{ __('You are not allowed to see project details.') }}</p>
                            @endcan
                        @else
                            <p class="prose">{{ __('Project not identified') }}</p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-bold">{{ __('Details') }}</h4>
                        
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('File type') }}</span>{{ $document->mime }}</p>
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('Uploaded by') }}</span>{{ $document->uploader->name }}</p>
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('Team') }}</span>{{ $document->team?->name }}</p>
                        
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('Language') }}</span>
                        {{ $document->language?->toLanguageName() }}</p>
                        
                        @if ($importDocument)
                            <p>
                                <span class="text-xs uppercase block text-stone-700">{{ __('Imported from') }}</span>
                            </p>

                            <div class="flex border border-stone-800 bg-stone-100 rounded-sm overflow-hidden">
                                <span class="bg-stone-800 text-white px-2 py-1">
                                    {{ $importDocument->import?->source->name }}
                                </span>
                                <span class=" px-2 py-1">
                                    {{ $importDocument->source_path }}
                                </span>

                            </div>
                        @endif
                        
                    </div>

                    {{-- <div class="space-y-2">
                        <h4 class="font-bold">{{ __('Publication') }}</h4>

                        @if ($document->isPublished())
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Published at') }}</span>{{ $document->published_at }}</p>
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Published by') }}</span>{{ $document->published_by?->name }}</p>
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Reachable on') }}</span>{{ $document->published_to_url }}</p>
                        @else
                            <p class="prose">{{ __('Not yet published.') }}</p>
                        @endif
                    </div> --}}
                {{-- </div> --}}
            </div>
        </div>
    </div>

    @question()
        @can('viewAny', \App\Models\Question::class)
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <x-document-chat :document="$document" />
                </div>
            </div>
        @endcan
    @endquestion
</x-app-layout>
