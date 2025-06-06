<div>
    @if($fields->isEmpty())
        <div class="text-center py-8">
            <div class="mx-auto w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <p class="text-sm text-gray-500">{{ __('No fields defined') }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ __('Add fields to your catalog to define its structure.') }}</p>
            <x-button class="mt-4" x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-field-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                {{ __('Add Field') }}
            </x-button>
        </div>
    @else
        <div class="relative overflow-x-auto" x-on:field-created.window="$wire.$refresh()"  x-on:catalog-entry-added.window="$wire.$refresh()">

            <table class="w-full text-sm text-left" 
            >
                <thead class="text-xs text-stone-700 bg-stone-50 sticky top-0">
                    <tr>
                        <th scope="col" class=" font-normal whitespace-nowrap sticky left-0 bg-stone-50">
                            <x-popover>
                                    <x-slot name="trigger" class="font-normal px-6 py-3 whitespace-nowrap inline-flex gap-1 items-center hover:bg-stone-100">
        
                                        {{ __('No') }}
                                            
                                        <x-heroicon-m-ellipsis-horizontal class="ms-1 size-4" />
                                    </x-slot>
                                    
                                    <button wire:click="sortAscending(0)" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                        @if (blank($sort_by) && (blank($sort_direction) || $sort_direction === 'asc'))
                                            <span>THIS</span>
                                        @endif
                                        {{ __('Ascending') }}
                                    </button>
                                    <button wire:click="sortDescending(0)" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                        @if (blank($sort_by) && $sort_direction === 'desc')
                                            <span>THIS</span>
                                        @endif
                                        {{ __('Descending') }}
                                    </button>

                                </x-popover>
                        </th>
                        @foreach($fields as $field)
                            <th scope="col">
                                <x-popover>
                                    <x-slot name="trigger" class="font-normal px-6 py-3 whitespace-nowrap inline-flex gap-1 items-center hover:bg-stone-100">
                                
                                        <x-dynamic-component :component="$field->data_type->icon()" class="text-stone-500 size-3" />
        
                                        {{ $field->title }}
                                            
                                        <x-heroicon-m-ellipsis-horizontal class="ms-1 size-4" />
                                    </x-slot>
                                    
                                    <button wire:click="sortAscending({{ $field->order }})" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                        @if ($sort_by === $field->order && $sort_direction === 'asc')
                                            <span>THIS</span>
                                        @endif
                                        {{ __('Ascending') }}
                                    </button>
                                    <button wire:click="sortDescending({{ $field->order }})" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                        @if ($sort_by === $field->order && $sort_direction === 'desc')
                                            <span>THIS</span>
                                        @endif
                                        {{ __('Descending') }}
                                    </button>

                                    <div class="border-t border-stone-200"></div>

                                    <button wire:click="moveFieldLeft({{ $field->order }})" @disabled($field->order <= 1) class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-65">
                                        <x-codicon-arrow-small-left class="size-4 text-stone-600" />
                                        {{ __('Move left') }}
                                    </button>
                                    <button wire:click="moveFieldRight({{ $field->order }})" @disabled($field->order >= $fields->count()) class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-65">
                                        <x-codicon-arrow-small-right class="size-4 text-stone-600" />
                                        {{ __('Move right') }}
                                    </button>


                                </x-popover>

                            
                        
                            </th>
                        @endforeach
                        <th scope="col" class=" font-normal px-6 py-3 whitespace-nowrap">
                            {{ __('Document') }}
                        </th>
                        <th scope="col" class=" font-normal px-6 py-3 whitespace-nowrap">
                            {{ __('Project') }}
                        </th>
                        <th scope="col" class=" font-normal px-6 py-3 whitespace-nowrap">
                            {{ __('Creation date') }}
                        </th>
                        <th scope="col" class=" font-normal px-6 py-3 whitespace-nowrap">
                            {{ __('Last update date') }}
                        </th>
                        <th scope="col" class="pointer-events-none font-normal px-6 py-3 whitespace-nowrap sticky right-0">
                            
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr class="bg-white border-b hover:bg-gray-50 group">
                            <td class="px-6 py-4 sticky left-0 bg-white group-hover:bg-gray-50">
                                {{ $entry->entry_index }}
                            </td>
                            @foreach($fields as $field)
                                <td class="px-6 py-4">
                                    @php
                                        $value = $entry->catalogValues->first(function($value) use ($field) {
                                            return $value->catalogField->id === $field->id;
                                        });
                                    @endphp
                                    
                                    @if($value)
                                        @switch($field->data_type)
                                            @case(\App\CatalogFieldType::TEXT)
                                                {{ $value->value_text }}
                                                @break
                                            @case(\App\CatalogFieldType::NUMBER)
                                                {{ $value->value_float }}
                                                @break
                                            @case(\App\CatalogFieldType::DATETIME)
                                                {{ $value->value_date?->toDateString() }}
                                                @break
                                            @case(\App\CatalogFieldType::BOOLEAN)
                                                @if($value->value_bool)
                                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                                @break
                                            @case(\App\CatalogFieldType::SKOS_CONCEPT)
                                                {{ optional($value->skosConcept)->prefLabel }}
                                                @break
                                            @default
                                                {{ $value->value }}
                                        @endswitch
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-6 py-4">
                                &nbsp; {{-- document --}}
                            </td>
                            <td class="px-6 py-4">
                                &nbsp; {{-- Project --}}
                            </td>
                            <td class="px-6 py-4">
                                <x-date :value="$entry->created_at" />
                            </td>
                            <td class="px-6 py-4">
                                <x-date :value="$entry->updated_at" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap sticky right-0 bg-white group-hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    <x-secondary-button >
                                        {{ __('Edit') }}
                                    </x-secondary-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $fields->count() + 1 }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ __('No entries yet') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    @endif


</div>