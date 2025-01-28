<x-app-layout>
    <x-slot name="title">
        {{ $concept->pref_label }} {{ $vocabulary->pref_label }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading >

            <x-slot:title>
                <a href="{{ route('vocabularies.show', $vocabulary)}}">{{ $vocabulary->pref_label }}</a> > {{ $concept->pref_label }}
            </x-slot>

            <x-slot:actions>
                <x-button-link href="#">
                    {{ __('Edit') }}
                </x-button-link>
            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">

            <div class="flex gap-4">
    
                <div class="w-80 shrink-0 ">

                    <livewire:skos-scheme-tree :scheme="$vocabulary->id" :selected="$concept->id" />
            
                </div>
            
                <div>
                    <div class="grid md:grid-cols-3 gap-6 grid-flow-row-dense ">
                        <div class="col-span-2">
                            <h2 class="text-2xl font-medium mb-2">
                                {{ $concept->pref_label }}
                            </h2>

                            <p class="text-sm flex items-center flex-wrap gap-2 text-stone-600">
                                <span class="sr-only">{{ __('Also written as') }}:</span>
                    
                                @if ($concept->notation)
                                    <code class="font-mono text-xs px-1 py-0.5 rounded ring-1 ring-stone-300  text-stone-900 bg-white">{{ $concept->notation }}</code>
                                    <span class="text-stone-400" aria-hidden="true">&middot;</span>
                                @endif
                    
                                @foreach ($concept->alt_labels as $label)
                    
                                    <span>{{ $label }}</span>
                    
                                    @if (!$loop->last)
                                        <span class="text-stone-400" aria-hidden="true">&middot;</span>
                                    @endif
                                @endforeach
                    
                            </p>

                            <div class="mt-4">
                    
                                @if($concept->definition)
                                    <p class="">{{ $concept->definition }}</p>
                                @else
                                    <p class="text-stone-700 flex items-center gap-2">{{ __('No definition yet for this concept.') }} <x-small-button type="button">
                                        <x-heroicon-s-pencil class="text-stone-600 h-4 w-4" />
                                        {{ __('Edit')}}
                                    </x-small-button></p>
                                @endif
                            </div>

                            
                        </div>
                        <div x-data class="space-y-3">
                            <h4 class="mb-3 text-xs tracking-widest text-gray-500 uppercase  sr-only">Concepts from other vocabularies</h4>
                
                            @foreach ($mappingMatches as $schemeName => $items)

                                <h4 class="mb-3 text-xs tracking-widest text-gray-500 uppercase">{{ $schemeName }}</h4>
                            
                                <ul class="flex flex-col gap-2 border-l border-stone-400/25">
                                    @foreach ($items as $item)
                                        
                                        <li class="-ml-px flex flex-col items-start gap-2">
                                            <a class="line-clamp-3 text-left text-sm border-l border-transparent text-gray-700 hover:border-gray-950/60 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4"
                                                x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $item->id }}'}})"
                                                href="{{ route('vocabulary-concepts.show', $item)}}">
                                                {{$item->pref_label}}
                                            </a>
                                        </li>
                                    @endforeach
                
                
                                </ul>
                            @endforeach
                
                        </div>
                        
                        <div class="col-span-3 grid grid-cols-3 gap-3">
                    
                            <details x-data>

                                {{-- make details element --}}
                    
                                <summary class="mb-3 text-xs tracking-widest text-gray-500 uppercase  ">Broader concepts</summary>
                    
                                <ul class="flex flex-col gap-2 border-l border-stone-400/25">
                                    @foreach ($concept->broader as $item)
                    
                                    <li class="-ml-px flex flex-col items-start gap-2">
                                        <a class="text-left text-sm border-l border-transparent text-gray-700 hover:border-stone-950/60  hover:text-stone-950  aria-[current]:border-stone-950 aria-[current]:font-semibold aria-[current]:text-stone-950  pl-5 sm:pl-4"
                                        x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $item->id }}'}})"
                                            href="{{ route('vocabulary-concepts.show', $item)}}">
                                            {{$item->pref_label}}
                                        </a>
                                    </li>
                    
                                    @endforeach
                                </ul>
                    
                            </details>
                   
                            <details x-data>

                                {{-- make details element --}}
                    
                                <summary class="mb-3 text-xs tracking-widest text-gray-500 uppercase  ">Narrower concepts</summary>
                    
                                <ul class="flex flex-col gap-2 border-l border-stone-400/25">
                                    @foreach ($concept->narrower as $item)
                    
                                    <li class="-ml-px flex flex-col items-start gap-2">
                                        <a class="text-left text-sm border-l border-transparent text-gray-700 hover:border-gray-950/60 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4"
                                        x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $item->id }}'}})"
                                            href="{{ route('vocabulary-concepts.show', $item)}}">
                                            {{$item->pref_label}}
                                        </a>
                                    </li>
                    
                                    @endforeach
                                </ul>
                    
                            </details>

                        </div>

                        <div class="col-start-1 col-span-3">
                            
                            <div class="flex mt-3 items-center justify-between">
                                
                                <div>
                                    <h4 class="text-xs tracking-widest text-gray-500 uppercase">Latest documents</h4>
                                </div>

                                <div class="flex items-center justify-end divide-x divide-stone-200 space-x-4">
                                    @if ($concept->documents->isNotEmpty())
                                        <div class="text-sm py-2 sm:text-right truncate">{{ trans_choice(':total document|:total documents', $concept->documents_count, ['total' => $concept->documents_count]) }}</div>
                                    @endif
                                    <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />
                                </div>
                            </div>

                            @php
                                $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
                            @endphp

                            <x-dynamic-component :component="$visualizationStyle" class="mt-3" :documents="$concept->documents">
                                <x-slot:empty>
                                    <div class=" text-stone-700 bg-white/40 gap-2 rounded overflow-hidden py-4 px-10 sm:col-span-2">
                                        <p class="flex flex-col justify-center items-center">
                                            <x-heroicon-o-document class="text-stone-500 size-10" />
                                            <span>
                                                {{ __('No documents are directly labeled with the term ":concept".', ['concept' => $concept->pref_label]) }}
                                            </span>
                                        </p>
                                        <p class="text-center">
                                            <a class="underline" target="_blank" href="{{ route('documents.library', ['s' => "\"{$concept->pref_label}\""]) }}">{{ __('Expand the search to include documents labeled with sub-terms of ":concept".', ['concept' => $concept->pref_label]) }} </a>
                                        </p>
                                    </div>
                                </x-slot>
                            </x-dynamic-component>




                            <p class="text-right mt-2"><a href="{{ route('documents.library', ['s' => "\"{$concept->pref_label}\""]) }}">View all</a></p>
                        </div>
                    
                    </div>
                </div>
                
            </div>
            
            

        </div>
    </div>
</x-app-layout>
