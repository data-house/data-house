<x-app-layout>
    <x-slot name="title">
        {{ $collection->title }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight space-y-2 sm:space-y-0 sm:flex sm:gap-4 md:items-center">
                {{ $collection->title }}

                <x-document-visibility-badge class="ml-4" :value="$collection->visibility" />
            </h2>
            <div class="flex gap-2">

                @can('viewAny', \App\Models\Collection::class)

                    <livewire:collection-switcher />

                @endcan
                
                @can('update', $collection)
                    <x-button-link href="{{ route('collections.edit', $collection) }}">
                        {{ __('Edit') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="bg-white/80 py-3 shadow"  x-data="{ expanded: false }">
        {{-- Collection expandable details --}}

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="" x-show="!expanded" x-collapse>
                <div class="flex items-center gap-8">
                    <div class="grow prose max-w-none line-clamp-1 mt-1">
                        @if ($notes->first())
                            {{ $notes->first()->previewContent() }}
                        @endif
                    </div>
                    <x-small-button class="shrink-0" @click="expanded = ! expanded">{{ __('Expand collection details') }}</x-small-button>
                </div>
            </div>

            <div x-cloak x-show="expanded" x-collapse>
                <div class="mb-6 space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2 items-center">
                            
                        </div>

                        <div>
                            <x-small-button @click="expanded = ! expanded">{{ __('Close collection details') }}</x-small-button>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4  pb-12">
                    <div class="space-y-4 col-span-2">
                        @foreach ($notes as $note)
                            <div class="prose">
                                {{ $note }}
                            </div>
                        @endforeach
                    </div>
                    <div class="">

                        <div class="space-y-2">
                            <p class="text-xs uppercase block text-stone-700">{{ __('Documents') }}</p>

                            <div class="">{{ trans_choice(':total document|:total documents', $total_documents, ['total' => $total_documents]) }}</div>

                        </div>

                        <x-section-border />

                        <div class="space-y-2">
                            <p class="text-xs uppercase block text-stone-700">{{ __('Contact') }}</p>


                            <div class="">

                                @if ($owner_team)
                                    <div class="flex items-center gap-1 ">
                                        <div class="rounded-xl h-10 w-10 object-cover shadow flex items-center justify-center bg-stone-200">
                                            <x-heroicon-o-users class="w-6 h-6 text-stone-600" />
                                        </div>
                                        
                                        {{ $owner_team->name }} {{ __('by') }} {{ $owner_user->name }}
                                    </div>
                                @else
    
                                    <div class="flex items-center gap-1 ">
                                        <div class="rounded-full h-10 w-10 object-cover shadow flex items-center justify-center bg-stone-200">
                                            <x-heroicon-o-user class="w-6 h-6 text-stone-600" />
                                        </div>
                                        
                                        {{ $owner_user->name }}
                                    </div>
    
                                @endif
                            </div>

                        </div>
                        
                        <x-section-border />
                        
                        <div class="space-y-2">
                            <p class="text-xs uppercase block text-stone-700">{{ __('Created on') }}</p>
                            <div class="prose">
                                {{ $collection->created_at->format('d F Y') }}
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @question()
            @feature(Flag::questionWithAI())
            <div class="space-y-2">
                <div class="flex justify-between mt-2 relative">
                    <div class="" x-data="{ open: false }" x-trap="open" @click.away="open = false" @close.stop="open = false">
                        <button type="button" @click="open = ! open" class="rounded px-2 py-1 text-sm text-lime-700 flex items-center gap-1 border border-transparent hover:bg-lime-100 hover:border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 focus:bg-lime-100 focus:border-lime-500">
                            <x-heroicon-s-sparkles class="text-lime-500 h-6 w-6" />
                            {{ __('Ask a question to all documents in this collection...') }}
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

                                <livewire:multiple-question-input :collection="$collection" :strategy="\App\Models\CollectionStrategy::STATIC" />
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($questions as $question)
                        <x-question-card :question="$question" />
                    @endforeach
                </div>
            </div>
            @endfeature
            @endquestion

            <div class="flex space-x-4 mt-3 divide-x divide-stone-200 items-center justify-end">

                @if ($documents->isNotEmpty())
                    <div class="text-sm py-2 text-right">{{ trans_choice(':total document in the collection|:total documents in the collection', $total_documents, ['total' => $total_documents]) }}</div>
                @endif

                <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />
            </div>

            @php
                $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
            @endphp

            <x-dynamic-component :component="$visualizationStyle" class="mt-3" :documents="$documents" empty="{{ __('Collection is empty') }}" />
            
            
            <div class="mb-4">
                @if ($collection->draft)
                    <span class="inline-block text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">{{ __('pending review') }}</span>
                @endif
            </div>
        </div>
    </div>

    @can('viewAny', \App\Models\Question::class)
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- TODO: shows questions related to this collection --}}

                {{-- <x-collection-chat :collection="$collection" /> --}}
            </div>
        </div>
    @endcan
</x-app-layout>
