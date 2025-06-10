<x-app-layout>
    <x-slot name="title">
        {{ __('Catalogs') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Catalogs')">

            <x-slot:actions>

                <div class="flex space-x-4 divide-x divide-stone-200 items-center">
                    <div class="text-sm py-2 sm:text-right truncate">
                        @if ($is_search)
                            {{ trans_choice(':total catalog found|:total catalogs found', $catalogs->total(), ['total' => $catalogs->total()]) }}
                        @endif
            
                        @if (!$is_search)
                            {{ trans_choice(':total catalog|:total catalogs', $catalogs->total(), ['total' => $catalogs->total()]) }}
                        @endif
                    </div>

                    {{-- <div class="pl-4">
                        <x-sorting-dropdown model="\App\Models\Catalog" />
                    </div> --}}

                    <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />
                </div>

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

                    @php
                        $visualizationStyle = 'catalog-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
                    @endphp

                    <x-dynamic-component :component="$visualizationStyle" class="mt-3" :catalogs="$catalogs">
                        <x-slot name="empty">
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
                        </x-slot>
                    </x-dynamic-component>
            
                    <div class="mt-2">{{ $catalogs?->links() }}</div>

                </div>


                <div class="hidden lg:block lg:col-span-4 xl:col-span-3 row-start-1 lg:col-start-9 xl:col-start-10 row-span-2">

                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Tips & Features') }}</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <x-dynamic-component
                                        :component="$hint_create_done ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-right-circle'"
                                        @class(['size-6', 'text-green-600' => $hint_create_done, 'text-stone-700' => !$hint_create_done ]) />
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Organize') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Create catalogs to analyze and combine data from documents and projects.') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <x-dynamic-component
                                        :component="$hint_structure_done ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-right-circle'"
                                        @class(['size-6', 'text-green-600' => $hint_structure_done, 'text-stone-700' => !$hint_structure_done ]) />
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Structure') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Create fields to organize data and provide structure, like a database.') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <x-dynamic-component
                                        :component="$hint_share_done ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-right-circle'"
                                        @class(['size-6', 'text-green-600' => $hint_share_done, 'text-stone-700' => !$hint_share_done ]) />
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ __('Collaborate') }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Make catalogs visible to your teammates or all users.') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>

                        
                    
        </div>
    </div>
</x-app-layout>
