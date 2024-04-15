
<div class="relative" x-data="{ open: false }" x-trap="open" @click.away="open = false" @close.stop="open = false" @keydown.escape="open = false">
    <x-secondary-button @click="open = ! open">
        <span>{{ __('Sort by') }}</span>

        <span class="font-bold">
            {{ trans("sorting.{$current}") }}
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
            class="absolute z-50 mt-2 w-48 rounded-md shadow-lg border border-stone-300/40 origin-top-right right-0 "
            style="display: none;">
        <div class="rounded-md ring-1 ring-black ring-opacity-5  py-1 bg-white">
            @foreach ($options as $option => $url)
                <x-dropdown-link :active="$option === $current" :href="$url">
                    {{ trans("sorting.{$option}") }}
                </x-dropdown-link>
            @endforeach
        </div>
    </div>
</div>
