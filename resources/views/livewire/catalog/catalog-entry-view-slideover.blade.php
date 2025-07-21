
<x-slideover  :title="__('Entry :index', ['index' => $catalog_entry->entry_index])" description="">
    
    <div class="h-4"></div>


    <div class="space-y-4 md:grid md:grid-cols-2 xl:grid-cols-3 gap-4">

        @if ($catalog_entry->trashed())
            <div class="py-2 px-2 flex gap-1 items-center min-w-0 md:col-span-2 xl:col-span-3 bg-stone-100 rounded">
                <x-heroicon-c-trash class="size-4 text-current" />

                <p class="ml-3 font-medium text-sm">{{ __('You are looking at a trashed entry.') }}

                    @can('restore', $catalog_entry)
                        <x-small-button wire:click="restoreEntry">
                            <span wire:loading.remove wire:target="restoreEntry">{{ __('Restore') }}</span>
                            <span wire:loading wire:target="restoreEntry">{{ __('Restoring...') }}</span>
                        </x-small-button>
                    @endcan

                </p>
            </div>            
        @endif
        
        <div class="flex flex-col gap-4 xl:col-span-2">
        @foreach($fields as $field)
            <div class="">
                @php
                    $value = $catalog_entry->catalogValues->first(function($value) use ($field) {
                        return $value->catalogField->id === $field->id;
                    });
                @endphp

                
                <p class="mb-1 font-medium text-sm text-stone-700">{{ $field->title }}</p>
                
                @if($value)
                    @switch($field->data_type)
                        @case(\App\CatalogFieldType::NUMBER)
                            {{ $value->value_float }}
                            @break
                        @case(\App\CatalogFieldType::DATETIME)
                            {{ $value->value_date?->toDateString() }}
                            @break
                        @case(\App\CatalogFieldType::BOOLEAN)
                            @if($value->value_bool)
                                <x-heroicon-s-check-circle class="w-5 h-5 text-green-600" />
                            @endif
                            @break
                        @case(\App\CatalogFieldType::SKOS_CONCEPT)
                            @if ($value->concept)
                                @feature(Flag::vocabulary())
                                    <a wire:navigate href="{{ route('vocabulary-concepts.show', $value->concept) }}" class="hover:underline">{{ $value->concept->pref_label }}</a>
                                @else
                                    <span class="block max-w-52 truncate">{{ $value->concept->pref_label }}</span>
                                @endfeature
                            @endif
                            @break
                        @default
                            <p>{{ $value->value_text }}</p>
                    @endswitch
                @endif
                
                @can('update', $catalog_entry)
                    @if ($field->flows->isNotEmpty())
                        <div class="text-sm mt-1">
                            @foreach ($field->flows as $flow)
                                <p>
                                    <x-small-button wire:click="triggerFlow('{{ $flow->uuid }}')"><x-heroicon-c-sparkles class="size-3" />
                                        <span wire:loading wire:target="triggerFlow('{{ $flow->uuid }}')">{{ __('Executing :flow...', ['flow' => $flow->title]) }}</span>
                                        <span wire:loading.remove wire:target="triggerFlow('{{ $flow->uuid }}')">{{ $flow->title }}</span>
                                    </x-small-button>
                                </p>
                            @endforeach
                        </div>
                    @endif
                @endcan
            </div>
        @endforeach
        </div>


        <div class="flex flex-col gap-6">
            <div class="">
                <p class="mb-1 font-medium text-sm text-stone-700">{{ __('Document') }}</p>

                @if ($catalog_entry->document)
                    <div class="space-y-2 group relative">
                        <div class="aspect-video flex items-center justify-center overflow-hidden">
                            {{-- Space for the thumbnail --}}
                            @if ($catalog_entry->document->hasThumbnail())
                            <img loading="lazy" class="aspect-video object-contain" src="{{ $catalog_entry->document->thumbnailUrl() }}" aria-hidden="true">
                            @else
                            <x-dynamic-component :component="$catalog_entry->document->format->icon" class="text-gray-400 h-10 w-10" />
                                @endif
                            </div>
                            
                            <a href="{{ route('documents.show', $catalog_entry->document) }}" class="block font-bold truncate group-hover:text-blue-800">
                                <span class="z-10 absolute inset-0"></span>{{ $catalog_entry->document->title }}
                            </a>
                            <div class="flex gap-2 justify-start">
                                
                                @if ($catalog_entry->document->format)
                                    <span class="inline-block text-xs px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">{{ $catalog_entry->document->format->name }}</span>
                                @endif
                                
                                @feature(Flag::editDocumentVisibility())
                                    <x-document-visibility-badge :value="$catalog_entry->document->visibility" />
                                @endfeature
                                &nbsp;
                            </div>
                    </div>
                @else
                    <p class="text-sm text-stone-600">{{ __('No Document linked to this entry.') }}</p>
                @endif
            </div>

            <div class="">
                <p class="mb-1 font-medium text-sm text-stone-700">{{ __('Project') }}</p>

                @if ($catalog_entry->project)
                    <x-project-card :project="$catalog_entry->project" />
                    {{-- <a wire:navigate href="{{ route('projects.show', $catalog_entry->project) }}"  class="block max-w-52 truncate hover:underline">{{ $catalog_entry->project->title }}</a> --}}
                @else
                    <p class="text-sm text-stone-600">{{ __('No Project linked to this entry.') }}</p>
                @endif
            </div>


            <div class="text-sm space-y-2">


                <p>
                    <x-date :value="$catalog_entry->updated_at" /> {{ __('by') }} {{ $catalog_entry->lastUpdatedBy?->name ?? $catalog_entry->user?->name }}
                </p>

                @if ($catalog_entry->trashed())
                    <p>
                        {{ __('Trashed at') }} <x-date :value="$catalog_entry->trashed_at" /> {{ __('by') }} {{ $catalog_entry->trashedBy?->name ?? $catalog_entry->user?->name }}
                    </p>
                @endif
                
            </div>
        </div>
                            


    </div>

    
    <x-slot name="actions">
        @can('update', $catalog_entry)
            {{-- <x-button  type="button" x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.edit-catalog-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                {{ __('Modify Entry (coming soon)') }}
            </x-button> --}}
        @endcan
    </x-slot>
    
    
</x-slideover>