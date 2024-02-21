@props(['action', 'clear', 'searchQuery', 'searchPlaceholder' => __('Search...'), 'appliedFiltersCount' => 0 ])

<div {{ $attributes }}>
    <form action="{{ $action }}" method="get" x-data="{showFilters: false}" @click.away="showFilters = false" @close.stop="showFilters = false">
        <div class="flex space-x-6 divide-x divide-stone-200 items-center">
            @unless ($filters->isEmpty())
                <div>
                    <button type="button"  @click="showFilters = ! showFilters" class="group flex items-center font-medium text-stone-700" aria-controls="disclosure-1" aria-expanded="false">
                        @if ($appliedFiltersCount > 0)
                            <x-heroicon-s-funnel aria-hidden="true" class="mr-2 h-5 w-5 flex-none text-stone-400 group-hover:text-stone-500" />
                        @else
                            <x-heroicon-o-funnel aria-hidden="true" class="mr-2 h-5 w-5 flex-none text-stone-400 group-hover:text-stone-500" />
                        @endif
                        
                        {{ trans_choice('{0} Filters|{1} :num Filter|[2,*] :num Filters', $appliedFiltersCount, ['num' => $appliedFiltersCount])}}
                    </button>
                </div>
            @endunless
            <div class="pl-6 flex-grow">
                <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ $searchPlaceholder }}" />
            </div>
        </div>


        <div class="border-b border-gray-200 py-10" id="disclosure-1" x-cloak x-show="showFilters">
            <div class="mx-auto grid max-w-7xl grid-cols-4 gap-x-4 px-4 text-sm sm:px-6 md:gap-6 lg:px-8">
                {{-- Slot for filters --}}
                {{ $filters }}
                
            </div>

            <div class="flex items-center justify-between mt-2">
                <x-button-link :href="$clear">{{ __('Clear search and filters') }}</x-button-link>
                <x-button type="submit">{{ __('Apply and search') }}</x-button>
            </div>
            </div>
    </form>

    {{ $slot }}
    
</div>