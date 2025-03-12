<x-app-layout>
    <x-slot name="title">
        {{ __('Your stars') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Your stars')">

            <x-slot:actions>

            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8">
            
            <x-search-form
                action=""
                :clear="route('stars.index')"
                :search-query="$searchQuery ?? null"
                :search-placeholder="__('Search your stars...')"
                :applied-filters-count="0"
                >

            </x-search-form>

            <div class="flex space-x-4 mt-3 divide-x divide-stone-200 items-center justify-stretch sm:justify-end">
                <div class="text-sm py-2 sm:text-right truncate">
                    @if ($is_search)
                        {{ trans_choice(':total star found|:total stars found', $stars->total(), ['total' => $stars->total()]) }}
                    @endif
        
                    @if (!$is_search)
                        {{ trans_choice(':total star|:total stars', $stars->total(), ['total' => $stars->total()]) }}
                    @endif
                </div>

                <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />
            </div>

            @php
                $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
            @endphp

            <x-dynamic-component :component="$visualizationStyle" class="mt-3" :documents="$documents" empty="{{ $is_search ? __('No stars matching the search criteria.') :__('No stars yet.') }}" />
            
            <div class="mt-2">{{ $stars?->links() }}</div>
        </div>
    </div>
</x-app-layout>
