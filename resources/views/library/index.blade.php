<x-app-layout>
    <x-slot name="title">
        {{ __('Digital Library') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Digital Library')">

            <x-slot:actions>
                @feature(Flag::collections())
                    @can('viewAny', \App\Models\Collection::class)

                        <livewire:collection-switcher />

                    @endcan
                @endfeature

                <x-add-documents-button />
            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8">                
            
            @include('document.partials.search')

            <div class="flex space-x-4 mt-3 divide-x divide-stone-200 items-center justify-stretch sm:justify-end">
                <div class="text-sm py-2 sm:text-right truncate">
                @if ($is_search)
                    {{ trans_choice(':total document found|:total documents found', $documents->total(), ['total' => $documents->total()]) }}
                @endif
    
                @if (!$is_search)
                    {{ trans_choice(':total document in the library|:total documents in the library', $documents->total(), ['total' => $documents->total()]) }}
                @endif
                </div>
                
                <div class="pl-4">
                    <x-sorting-dropdown model="\App\Models\Document" />
                </div>

                <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />

            </div>

            @php
                $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
            @endphp

            <x-dynamic-component :component="$visualizationStyle" class="mt-3" :documents="$documents" empty="{{ $is_search ? __('No documents matching the search criteria.') :__('No documents in the library') }}" />
            
            <div class="mt-2">{{ $documents?->links() }}</div>
        </div>
    </div>
</x-app-layout>
