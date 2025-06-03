<x-app-layout>
    <x-slot name="title">
        {{ $catalog->title }} - {{ __('Catalogs') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="$catalog->title">

            <x-slot:actions>
                @can('create', \App\Models\CatalogEntry::class)
                    <x-button x-data  x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-entry-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})" size="sm">
                        {{ __('Add Entry') }}
                    </x-button>
                @endcan
                @can('create', \App\Models\CatalogField::class)
                    <x-secondary-button x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-field-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                        {{ __('Add Field') }}
                    </x-secondary-button>
                @endcan
            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8 ">
                    
            <livewire:catalog.catalog-datatable :catalog="$catalog" />
        </div>
    </div>
</x-app-layout>
