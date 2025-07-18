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

                <livewire:document-flow-action-button :document="$document" />
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

                    @if ($concepts->isNotEmpty())

                        <div class="space-y-3" x-data>
                            <h4 class="font-bold text-stone-700">{{ __('Linked concepts') }}</h4>

                            <ul class="flex flex-wrap gap-2">
                                @foreach ($concepts ?? [] as $concept)
                                    <li><a class="text-sm inline-flex gap-2 py-0.5 px-2 items-center rounded-md bg-stone-50 ring-stone-300 hover:bg-indigo-200 ring-1 hover:ring-indigo-500"
                                    x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $concept->id }}'}})"
                                    href="{{route('vocabulary-concepts.show', $concept->id) }}" 
                                    >{{ $concept->pref_label }}</a></li>                    
                                @endforeach
                            </ul>

                            @if ($remaining_concepts?->isNotEmpty() ?? false)

                                <div class="space-y-2" x-data="{expand: false}">

                                    <x-small-button  @click="expand = !expand">

                                        <x-heroicon-o-ellipsis-horizontal class="size-4 text-stone-600" />
                                     {{ trans_choice(':value other concept|:value other concepts', $remaining_concepts->count(), ['value' => $remaining_concepts->count()]) }}
                                    </x-small-button>

                                
                                    <ul x-cloak x-show="expand" class="flex flex-wrap gap-2">
                                        @foreach ($remaining_concepts as $concept)
                                        <li><a class="text-sm inline-flex gap-2 py-0.5 px-2 items-center rounded-md bg-stone-50 ring-stone-300 hover:bg-indigo-200 ring-1 hover:ring-indigo-500"
                                            x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $concept->id }}'}})"
                                            href="{{route('vocabulary-concepts.show', $concept->id) }}" 
                                            >{{ $concept->pref_label }}</a></li>                    
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <x-section-border />

                    @endif

                    @if ($sdg && $sdgConcept)
                        <div class="space-y-3" x-data>
                            <h4 class="font-bold text-stone-700">{{ __('Sustainable Development Goals') }}</h4>
                            
                            <div class="grid grid-cols-1 grid-rows-1 bg-white p-2 rounded">
                                <div class="col-start-1 row-start-1 p-3 z-10">
                                    <p>
                                        <span style="color: {{trans("sdg.{$sdg['name']}.color")}}">{{ __('Goal :value', ['value' => trans("sdg.{$sdg['name']}.goal")]) }}</span>
                                        <span class="font-medium">{{ trans("sdg.{$sdg['name']}.label") }}</span>
                                    </p>
                                    <p class="text-xs text-stone-700">{{ trans("sdg.{$sdg['name']}.title") }}</p>
                                </div>
                                <a class="col-start-1 row-start-1 z-20 block w-full h-full"
                                    x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $sdgConcept->id }}'}})"
                                    href="{{route('vocabulary-concepts.show', $sdgConcept->id) }}" 
                                    title="See in vocabulary">&nbsp;</a>
                            </div>
                            
                            <div class="relative h-2 rounded-md flex overflow-hidden gap-0.5">
                                @foreach ($sdg_stats as $classification)
                                    <div class="h-2 grow-0 opacity-80"
                                        style="background: {{ $classification['color'] }};width:{{ $classification['score']*100 }}%"
                                        x-data x-tooltip.raw="{{ $classification['goal'] . ' - ' . $classification['percentage'] }}"
                                        >
                                    </div>
                                @endforeach
                            </div>
                        
                            <div class="flex gap-4 flex-wrap">
                                @foreach ($sdg_stats as $classification)
                                    <span class="text-xs inline-flex items-center gap-1" x-data x-tooltip.raw="{{ $classification['title'] }}">
                                        <span class="rounded-full w-3 h-3" style="background: {{ $classification['color'] }}"></span>
                                        @if (($classification['name'] ?? false) && ($sdgConcepts[$classification['name']] ?? false))
                                            @php
                                                $currentConcept = $sdgConcepts[$classification['name']];
                                            @endphp
                                            <a class="font-medium text-stone-800 underline"
                                                x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $currentConcept->id }}'}})"
                                                href="{{ route('vocabulary-concepts.show', $currentConcept->id) }}">{{ $classification['goal'] }}</a>
                                        @else
                                            <span class="font-medium text-stone-800">{{ $classification['goal'] }}</span>
                                        @endif
                                        <span class="text-stone-600">{{ $classification['percentage'] }}</span>
                                    </span>
                                @endforeach
                            </div>

                            <p class="text-xs text-stone-700 flex items-center gap-1">
                                <x-heroicon-s-sparkles class="text-stone-500 h-4 w-4 line-clamp-2" />
                                {{ __('The SDG classification is automatically generated.') }}
                            </p>
                        </div>

                        <x-section-border />
                        
                    @endif


                    <div class="space-y-3">
                        <h4 class="font-bold text-stone-700">{{ __('Project') }}</h4>

                        <livewire:document-project :document="$document" />

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
