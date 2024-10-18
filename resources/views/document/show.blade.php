<x-app-layout>
    <x-slot name="title">
        {{ $document->title }}
    </x-slot>
    <x-slot name="header">
        <div class="space-y-2 md:space-y-0 flex flex-col gap-2 relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight space-y-2 sm:space-y-0 flex gap-2 md:gap-4 items-center whitespace-nowrap">
                <x-dynamic-component :component="$document->format->icon" class="text-gray-400 h-7 w-7 shrink-0" />
                
                <span class="min-w-0 truncate">{{ $document->title }}</span>

                @feature(Flag::editDocumentVisibility())
                <x-document-visibility-badge class="ml-4" :value="$document->visibility" />
                @endfeature
            </h2>
            <div class="flex gap-2 flex-wrap md:flex-nowrap items-center">
                @can('create', \App\Models\Star::class)
                    <livewire:star-button :model="$document" />
                @endcan
                @can('view', $document)
                    @if ($document->hasPreview())
                        <x-button-link href="{{ $document->viewerUrl() }}" target="_blank">
                            <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4 shrink-0 " /> {{ __('Preview') }}
                        </x-button-link>
                    @endif
                @endcan
                @can('view', $document)
                    <x-button-link href="{{ $document->url() }}" target="_blank">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4 shrink-0 " /> {{ __('Download') }}<span class="hidden sm:inline"> ({{ $document->format->extension }})</span>
                    </x-button-link>
                @endcan
                @can('update', $document)
                    <x-button-link href="{{ route('documents.edit', $document) }}">
                        {{ __('Edit') }}
                    </x-button-link>

                    <div class="hidden md:block">
                        @feature(Flag::editDocumentVisibility())
                        <livewire:document-visibility-selector :document="$document" />
                        @endfeature
                    </div>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="">

        @if ($hasActivePipelines)
                <div class="max-w-7xl mx-auto my-2 py-2 bg-yellow-100 text-yellow-900 flex items-center gap-2 px-4 sm:px-6 lg:px-8">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 shrink-0">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                      </svg>
                      
                    <span class="">{{ __('Document under processing. Preview and search might not be available while the document is being processed.') }}</span>
                </div>
            @endif


        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            

            <div class="flex flex-col md:flex-row">

                <div class="col-span-2 md:basis-3/5 shrink-0 mb-12">

                    <livewire:document-summaries-viewer :show-create="true" :document="$document" />

                    @if ($document->sections->isNotEmpty())
                        
                        <h4 class="mt-8 font-bold mb-2 text-stone-700">
                            {{ __('Content preview') }}
                        </h4>

                        <ul class="pr-4">
                            @foreach ($document->sections as $section)
                            <li class="mb-2">
                                <a href="{{ $document->viewerUrl($section->page())}}" class="text-sm flex items-center gap-2 group hover:text-blue-800" target="_blank">
                                    {{ $section->title }}

                                    <span class="grow h-px bg-stone-200 group-hover:bg-stone-300"></span>
                                    <span>{{ $section->page() }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                    
                </div>

                <div class="flex flex-col gap-6 md:gap-0 grow">
                    @feature(Flag::collections())
                        <div class="space-y-2">
                            <h4 class="font-bold text-stone-700">{{ __('Collections') }}</h4>
                            
                            <livewire:document-collections :document="$document" />
                        </div>

                        <x-section-border />
                    @endfeature

                    <div class="space-y-3">
                        <h4 class="font-bold text-stone-700">{{ __('Project') }}</h4>
                        
                        @if ($document->project)
                            <x-project-card :project="$document->project" />
                        @else
                            <p class="prose">{{ __('Project not identified') }}</p>
                        @endif
                    </div>

                    <x-section-border />

                    <div>
                        <h4 class="font-bold mb-2 text-stone-700">{{ __('Contact') }}</h4>

                        <div class="">

                            @if ($document->team)
                                <div class="flex items-center gap-1 ">
                                    <div class="rounded-xl h-10 w-10 object-cover shadow flex items-center justify-center bg-stone-200">
                                        <x-heroicon-o-users class="w-6 h-6 text-stone-600" />
                                    </div>
                                    
                                    {{ $document->team->name }}
                                </div>
                            @else

                                <div class="flex items-center gap-1 ">
                                    <div class="rounded-full h-10 w-10 object-cover shadow flex items-center justify-center bg-stone-200">
                                        <x-heroicon-o-user class="w-6 h-6 text-stone-600" />
                                    </div>
                                    
                                    {{ $document->uploader->name }}
                                </div>

                            @endif
                        </div>
                    </div>

                    <x-section-border />

                    <div>
                        <h4 class="font-bold mb-2 text-stone-700">{{ __('File details') }}</h4>
                        
                        <div class="space-y-5">
                            <div>
                                <span class="text-xs block mb-1 text-stone-700">{{ __('Language') }}</span>
                                <x-language-card :language="$document->language" />
                            </div>

                            <div>
                                <span class="text-xs block text-stone-700">{{ __('Format') }}</span>
                                <x-file-format-card class="mt-1" :format="$document->format" />
                            </div>
                            <p>
                                <span class="text-xs block text-stone-700">{{ __('Size') }}</span>
                                {{ $document->size }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @question()
        @can('viewAny', \App\Models\Question::class)
            @if ($document->hasTextualContent())
                <div class="py-12">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <x-document-chat :document="$document" />
                    </div>
                </div>
            @endif
        @endcan
    @endquestion
</x-app-layout>
