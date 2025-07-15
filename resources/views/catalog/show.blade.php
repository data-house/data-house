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
                
                @can('viewAny', \App\Models\CatalogFlow::class)

                    <x-popover width="80">
                        <x-slot name="trigger" class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-stone-300 rounded-md font-semibold text-xs text-stone-700  shadow hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">

                            <x-heroicon-o-arrow-turn-down-right class="ms-1 size-4 text-stone-500" />

                            {{ __('Actions') }}

                            <x-heroicon-o-chevron-down class="size-4" />
                                
                        </x-slot>

                        @forelse ($flows as $flow)
                            <button type="button" x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.catalog-flow-run-slideover', arguments: {flow: '{{ $flow->getKey() }}'}})" class=" inline-flex items-center gap-1 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                                {{ $flow->title }}
                            </button>
                            
                        @empty
                            <p class="px-4 py-2 text-stone-600 text-sm">{{ __('No flows defined for this catalog.') }}</p>
                        @endforelse



                    </x-popover>
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
