<div class="inline-flex">
    
    <div class="relative" x-data="{ open: false }" x-trap="open" @click.away="open = false" @close.stop="open = false" @keydown.escape="open = false">
        <x-secondary-button @click="open = ! open" class="rounded-r-none">
            <span class="hidden sm:inline">{{ __('Sort by') }}</span>

            <span class="font-bold whitespace-nowrap">
                @if ($is_search)
                    {{ __('Best match')}}&nbsp;+&nbsp;
                @endif
                {{ trans("sorting.{$current->name}") }}
            </span>
            
            <x-heroicon-o-chevron-down class="w-5 h-5 transition-transform"  ::class="{'transform rotate-180': open }" />
        </x-secondary-button>

        <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute z-50 mt-2 w-full min-w-48 rounded-md shadow-lg border border-stone-300/40 origin-top-right right-0 "
                style="display: none;">
            <div class="rounded-md rounded-r-none ring-1 ring-black ring-opacity-5  py-1 bg-white">

                @if ($is_search)
                    <p class="px-4 py-2  prose prose-sm">{{ __('Sorting is applied after the best matches are found') }}</p>
                @endif

                @foreach ($options as $option)
                    <x-dropdown-link :active="$isCurrent($option)" :href="$url($option)">
                        {{ trans("sorting.{$option->name}") }}
                    </x-dropdown-link>
                @endforeach
            </div>
        </div>
    </div>
    <div>
        <a title="{{ __('Current direction: :direction. Click to invert.', ['direction' => trans("sorting.direction.{$current_direction}")])}}"
           href="{{ $url($current->invertDirection()) }}"
           class="inline-flex items-center gap-1 px-3 py-2 bg-white border-r border-y border-stone-300 rounded-md font-semibold text-xs text-stone-700  shadow hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 rounded-l-none">
           
            @if ($current_direction === 'desc')
                <x-heroicon-o-bars-arrow-down class="w-5 h-5" />
            @else
                <x-heroicon-o-bars-arrow-up class="w-5 h-5" />
            @endif
        </a>
    </div>
</div>