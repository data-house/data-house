<x-app-layout>
    <x-slot name="title">
        {{ $catalog->title }} - {{ __('Catalogs') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="$catalog->title">

            <x-slot:actions>
                @can('create', [\App\Models\CatalogEntry::class, $catalog])
                    <x-button x-data  x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-entry-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                        {{ __('Add Entry') }}
                    </x-button>
                @endcan
                @can('create', [\App\Models\CatalogField::class, $catalog])
                    <x-secondary-button x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-field-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                        {{ __('Create Field') }}
                    </x-secondary-button>
                @endcan

                @can('view', $catalog)
                    <x-secondary-button x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.catalog-info-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                        {{ __('Info') }}
                    </x-secondary-button>
                @endcan
                
            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-4 pb-12">
        <div class="px-4 sm:px-6 lg:px-8">                    
            <livewire:catalog.catalog-datatable :catalog="$catalog" />
        </div>
    </div>
</x-app-layout>
