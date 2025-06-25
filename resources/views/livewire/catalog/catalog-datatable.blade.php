<div>
    @if($fields->isEmpty())
        <div class="grid grid-cols-1 md:grid-cols-3">
        
            <div class="px-6 py-4 bg-white overflow-hidden shadow-sm rounded sm:rounded-lg md:col-span-2"  x-data>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Getting Started') }}</h3>
                <div class="flex flex-col gap-3">
                    <div class="space-y-3">
                        <p class="text-sm">{{ __('Fields define the backbone of your catalog providing safe and structured storage for your data. Fields for counting entries and connect with documents or projects are automatically added for you.') }}</p>

                        <ol class="list-decimal list-inside space-y-2 text-sm">
                            <li>{{ __('Add a first field') }}</li>
                            <li>{{ __('Save an entry') }}</li>
                            <li>{{ __('You can add more fields later') }}</li>
                        </ol>
                    </div>
                    @can('create', [\App\Models\CatalogField::class, $catalog])
                        <div class="flex-shrink-0">
                            <x-button class="mt-4" x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-field-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                                {{ __('Create a Field') }}
                            </x-button>
                        </div>
                    @endcan
                </div>
            </div>

            <div class="">

                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Don\'t know what to add?') }}</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-table-cells class="size-6 shrink-0 text-stone-500" />
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ __('Track your todos') }}</h4>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Create a todo list to track your activities.') }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ __('With three fields: activity, done/not done checkmark and a due date you can monitor your progress.') }}</p>
                                <p class="mt-1">
                                <x-small-button wire:click="generateTodoListExample">
                                    <span wire:loading.remove wire:target="generateTodoListExample">{{ __('Generate the todo list fields') }}</span>
                                    <span wire:loading wire:target="generateTodoListExample">{{ __('Preparing your todo list...') }}</span>
                                </x-small-button>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    @else
        <div class="relative  mb-4">
            <x-input type="text" name="catalog_s" wire:model.live.debounce.500ms="search" id="catalog_s" class="min-w-full" placeholder="{{ __('Search entries...') }}" />

            <div wire:loading wire:target="search" class="absolute top-0 right-0 flex items-center h-full p-2 text-orange-50 bg-orange-600 rounded-r-md ">
                {{ __('Searching...') }}
            </div>
        </div>

        <div class="relative overflow-x-auto" x-on:field-created.window="$wire.$refresh()"  x-on:catalog-entry-added.window="$wire.$refresh()">

            <table class="w-full text-sm text-left" 
            >
                <thead class="text-xs text-stone-700 bg-stone-50 sticky top-0">
                    <tr>
                        <th scope="col" class=" font-normal whitespace-nowrap sticky left-0 bg-stone-50">
                            <x-popover>
                                    <x-slot name="trigger" class="bg-stone-50 font-normal px-6 py-3 whitespace-nowrap flex w-full gap-1 items-center hover:bg-stone-100">
        
                                        {{ __('No') }}
                                            
                                        <x-heroicon-m-ellipsis-horizontal class="ms-1 size-4" />
                                    </x-slot>
                                    
                                    <button wire:click="sortAscending(0)" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                        @if (blank($sort_by) && (blank($sort_direction) || $sort_direction === 'asc'))
                                            <x-heroicon-m-check-circle class="size-4" />
                                        @endif
                                        {{ __('Ascending') }}
                                    </button>
                                    <button wire:click="sortDescending(0)" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                        @if (blank($sort_by) && $sort_direction === 'desc')
                                            <x-heroicon-m-check-circle class="size-4" />
                                        @endif
                                        {{ __('Descending') }}
                                    </button>

                                </x-popover>
                        </th>
                        <th scope="col" class=" font-normal px-6 py-3 whitespace-nowrap">
                            {{ __('Document') }}
                        </th>
                        <th scope="col" class=" font-normal px-6 py-3 whitespace-nowrap">
                            {{ __('Project') }}
                        </th>
                        @foreach($fields as $field)
                            <th scope="col" @class(['min-w-96' => $field->data_type === \App\CatalogFieldType::MULTILINE_TEXT])>
                                <x-popover>
                                    <x-slot name="trigger" class="font-normal px-6 py-3 whitespace-nowrap flex w-full gap-1 items-center hover:bg-stone-100">
                                
                                        <x-dynamic-component :component="$field->data_type->icon()" class="text-stone-500 size-3" />
        
                                        {{ $field->title }}
                                            
                                        <x-heroicon-m-ellipsis-horizontal class="ms-1 size-4" />
                                    </x-slot>

                                    @unless ($field->data_type->isReference())
                                        <button wire:click="sortAscending({{ $field->order }})" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                            @if ($sort_by === $field->order && $sort_direction === 'asc')
                                                <x-heroicon-m-check-circle class="size-4" />
                                            @endif
                                            {{ __('Ascending') }}
                                        </button>
                                        <button wire:click="sortDescending({{ $field->order }})" class="inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                            @if ($sort_by === $field->order && $sort_direction === 'desc')
                                                <x-heroicon-m-check-circle class="size-4" />
                                            @endif
                                            {{ __('Descending') }}
                                        </button>

                                        <div class="border-t border-stone-200"></div>
                                    @endunless
                                    

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
                            <td class="px-6 py-4">
                                @if ($entry->document)
                                    <a wire:navigate href="{{ route('documents.show', $entry->document) }}" class="block max-w-52 truncate hover:underline">{{ $entry->document->title }}</a>
                                @else
                                    &nbsp;
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($entry->project)
                                    <a wire:navigate href="{{ route('projects.show', $entry->project) }}"  class="block max-w-52 truncate hover:underline">{{ $entry->project->title }}</a>
                                @else
                                    &nbsp;
                                @endif
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
                                                    <a wire:navigate href="{{ route('vocabulary-concepts.show', $value->concept) }}" class="block max-w-52 truncate hover:underline">{{ $value->concept->pref_label }}</a>
                                                @endif
                                                @break
                                            @default
                                                {{ $value->value_text }}
                                        @endswitch
                                    @endif
                                </td>
                            @endforeach
                            
                            <td class="px-6 py-4">
                                <x-date :value="$entry->updated_at" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap sticky right-0 bg-white group-hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    <x-secondary-button wire:click="$dispatch(
                                'openSlideover', { 
                                    component: 'catalog.catalog-entry-view-slideover', 
                                    arguments: { 
                                        catalogEntry: '{{ $entry->getKey() }}'
                                    }
                                })">
                                        {{ __('Open') }}
                                    </x-secondary-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $fields->count() + 6 }}" class="px-6 py-4 text-center text-sm text-gray-500">
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