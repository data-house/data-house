<x-app-layout>
    <x-slot name="title">
        {{ $vocabulary->pref_label }} - {{ __('Vocabularies') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="$vocabulary->pref_label">

            <x-slot:actions>

            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8">

            <div class="flex gap-4">
    
                <div class="w-80 shrink-0 ">

                    <livewire:skos-scheme-tree :scheme="$vocabulary->id" />
            
                </div>
            
                <div class="space-y-6">

                    <div x-data>
                        {{-- Show recently added concepts --}}
                    
                            <h4 class="mb-3 text-xs tracking-widest text-gray-500 uppercase">Recently updated concepts</h4>
                
                            <ul class="flex flex-col gap-2 border-l border-stone-400/25">
                                @foreach ($recentConcepts as $item)
                
                                <li class="-ml-px flex flex-col items-start gap-2">
                                    <a class="text-left text-sm border-l border-transparent text-gray-700 hover:border-gray-950/60 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4"
                                    x-on:click.prevent="Livewire.dispatch('openSlideover', {component: 'concept-viewer-slideover', arguments: { concept: '{{ $item->id }}'}})"
                                        href="{{ route('vocabulary-concepts.show', $item)}}">
                                        {{$item->pref_label}}
                                    </a>
                                </li>
                
                                @endforeach
                            </ul>
                
                    </div>

                    <div class=""">
                        {{-- Show concepts with most documents --}}

                        <div class="flex mt-3 items-center justify-between">
                                
                            <div>
                                <h4 class="text-xs tracking-widest text-gray-500 uppercase">Most used concepts</h4>
                            </div>

                            <div class="flex items-center justify-end divide-x divide-stone-200 space-x-4">
                                
                                <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />
                            </div>
                        </div>

                        @php
                            $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
                        @endphp

                        @foreach ($topMostLinkedConcepts as $concept)

                            <div class="mb-6">
                                <p class="font-bold mb-2"><a class="flex items-center gap-2" href="{{ route('vocabulary-concepts.show', $concept)}}">{{ $concept->pref_label }} <span class="inline-flex size-6 rounded-full items-center justify-center text-xs bg-white">{{ $concept->documents_count }}</span></a></p>

                                <x-dynamic-component :component="$visualizationStyle" class="mt-3" :documents="$concept->documents" />

                                <p class="text-right mt-2"><a href="{{ route('documents.library', ['s' => "\"{$concept->pref_label}\""]) }}">View all</a></p>
                            </div>
                            
                        @endforeach
                    </div>


                    
                    


                </div>
            </div>          

        </div>
    </div>
</x-app-layout>
