<x-app-layout>
    <x-slot name="title">
        {{ __('Catalogs') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Catalogs')">

            <x-slot:actions>
                @can('create', \App\Models\Catalog::class)
                    <x-button x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-catalog-slideover'})">{{ __('Create a catalog') }}</x-button>
                @endcan
            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-12 gap-6">

                <div class="col-span-12  lg:col-span-8 xl:col-span-9">

                    @forelse ($catalogs as $catalog)

                        <a wire:navigate href="{{ route('catalogs.show', $catalog) }}" class="flex items-center space-x-4 hover:bg-gray-50 rounded-md -mx-2 p-2">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <x-heroicon-o-table-cells class="size-4" />
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
                        
                    @empty
                        <div class="px-6 py-4 bg-white overflow-hidden shadow-sm rounded sm:rounded-lg"  x-data>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Getting Started') }}</h3>
                            <div class="flex flex-col gap-3">
                                <div class="space-y-3">
                                    <p class="text-sm">{{ __('Turn documents into a powerful database. With catalogs you can organize everything from reading lists, recommendations, projects lifecycle, and more') }}</p>

                                    <ol class="list-decimal list-inside space-y-2 text-sm">
                                        <li>{{ __('Create a new catalog') }}</li>
                                        <li>{{ __('Add custom fields to capture information') }}</li>
                                        <li>{{ __('Share with your team or all users') }}</li>
                                    </ol>
                                </div>
                                @can('create', \App\Models\Catalog::class)
                                    <div class="flex-shrink-0">
                                        <x-button x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.create-catalog-slideover'})">
                                            {{ __('Create Your First Catalog') }}
                                        </x-button>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endforelse
                </div>


                <div class="hidden lg:block lg:col-span-4 xl:col-span-3 row-start-1 lg:col-start-9 xl:col-start-10 row-span-2">

                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Tips & Features') }}</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-check-circle @class(['size-6', 'text-green-600' => $catalogs->isNotEmpty(), 'text-stone-700' => $catalogs->isEmpty() ])  />
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Organize') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Create catalogs to combine data spread across documents.') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-check-circle @class(['size-6', 'text-green-600' => $catalogs->isNotEmpty(), 'text-stone-700' => $catalogs->isEmpty() ])  />
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Structure') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Add custom fields for better organization.') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-check-circle @class(['size-6', 'text-green-600' => $catalogs->isNotEmpty(), 'text-stone-700' => $catalogs->isEmpty() ])  />
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Collaborate') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Open catalogs to your team or all users.') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>

                        
                    
        </div>
    </div>
</x-app-layout>
