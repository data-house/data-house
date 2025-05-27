<x-app-layout>
    <x-slot name="title">
        {{ $catalog->title }} - {{ __('Catalogs') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="$catalog->title">

            <x-slot:actions>
                @can('create', \App\Models\CatalogEntry::class)
                    <x-button x-init  x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-entry-slideover', arguments: {catalogId: '{{ $catalog->getKey() }}'}})" size="sm">
                        {{ __('Add Entry') }}
                    </x-button>
                @endcan
                @can('create', \App\Models\CatalogField::class)
                    <x-button class="mt-4" x-init x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-field-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                        {{ __('Add Field') }}
                    </x-button>
                @endcan
            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-8">
                    
            <div class="grid grid-cols-12 gap-6">

                <div class="col-span-12  lg:col-span-8 xl:col-span-9 bg-white overflow-hidden shadow-sm sm:rounded-lg"  x-data>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Getting Started') }}</h3>
                        <div class="flex items-center justify-between">
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">{{ __('New to catalogs? Follow these steps:') }}</p>
                                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
                                    <li>{{ __('Create a new catalog for your documents') }}</li>
                                    <li>{{ __('Add custom fields to capture document metadata') }}</li>
                                    <li>{{ __('Upload and organize your documents') }}</li>
                                    <li>{{ __('Share with team members if needed') }}</li>
                                </ol>
                            </div>
                            @can('create', \App\Models\Catalog::class)
                                <div class="flex-shrink-0 hidden md:block">
                                    <x-button x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-catalog-slideover'})">
                                        {{ __('Create Your First Catalog') }}
                                    </x-button>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-8 xl:col-span-9">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Entries') }}</h3>
                                <div class="flex space-x-3">
                                    <x-button x-init  x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-entry-slideover', arguments: {catalogId: '{{ $catalog->getKey() }}'}})" size="sm">
                                        {{ __('Add Entry') }}
                                    </x-button>
                                </div>
                            </div>

                            @if($fields->isEmpty())
                                <div class="text-center py-8">
                                    <div class="mx-auto w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500">{{ __('No fields defined') }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Add fields to your catalog to define its structure.') }}</p>
                                    <x-button class="mt-4" x-init x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-field-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                                        {{ __('Add Field') }}
                                    </x-button>
                                </div>
                            @else
                                <div class="relative overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                @foreach($fields as $field)
                                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                        {{ $field->title }}
                                                        <div class="text-xs font-normal text-gray-500 uppercase">{{ $field->data_type->name }}</div>
                                                    </th>
                                                @endforeach
                                                <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                    {{ __('Actions') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($entries as $entry)
                                                <tr class="bg-white border-b hover:bg-gray-50">
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
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center space-x-3">
                                                            <button type="button" class="text-blue-600 hover:text-blue-900"
                                                                x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.edit-entry-slideover', arguments: {entryId: '{{ $entry->uuid }}'}})">
                                                                {{ __('Edit') }}
                                                            </button>
                                                            <button type="button" class="text-red-600 hover:text-red-900"
                                                                x-on:click="Livewire.dispatch('openModal', {component: 'catalog.delete-entry-modal', arguments: {entryId: '{{ $entry->uuid }}'}})">
                                                                {{ __('Delete') }}
                                                            </button>
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
                            @endif
                        </div>
                    </div>
                    
                        {{-- @if($catalogs->isEmpty())
                            <div class="text-center py-8" x-data>
                                <div class="mx-auto w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500">{{ __('No catalogs yet') }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ __('Create your first catalog to start structuring your data and documents.') }}</p>
                                @can('create', \App\Models\Catalog::class)
                                    <x-button class="mt-4" x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-catalog-slideover'})">
                                        {{ __('Create a catalog') }}
                                    </x-button>
                                @endcan
                            </div>
                        @else
                            <ul role="list" class="divide-y divide-gray-200">
                                @foreach($catalogs as $catalog)
                                    <li class="py-4">
                                        <a wire:navigate href="{{ route('catalogs.show', $catalog) }}" class="flex items-center space-x-4 hover:bg-gray-50 rounded-md -mx-2 p-2">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $catalog->title }}
                                                </p>
                                                <p class="text-sm text-gray-500 truncate">
                                                    {{ $catalog->description }}
                                                </p>
                                                <p class="text-sm text-gray-500 truncate">
                                                    {{ __('Last modified: :date', ['date' => $catalog->updated_at->diffForHumans()]) }}
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif --}}


                </div>


                <div class="hidden lg:block lg:col-span-4 xl:col-span-3 row-start-1 lg:col-start-9 xl:col-start-10 row-span-2">

                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Tips & Features') }}</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Organize Documents') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Create catalogs to group related documents and add custom fields for better organization.') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Custom Fields') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Add custom fields to capture specific information about your documents.') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Collaboration') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Share catalogs with team members and work together on document organization.') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
