<div class="inline-flex items-center bg-white border border-stone-300 rounded-md  text-stone-700 shadow  focus:outline-none focus:ring-2  focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 group/star focus:ring-yellow-500  hover:border-yellow-400">
    <button
        type="button"
        wire:click="toggle"
        class="font-semibold text-xs px-3 py-1 {{ $this->starCount > 0 ? 'border-r border-stone-300' : ''}}  flex gap-1 items-center rounded-l-md hover:bg-yellow-50 " >
    
        @if (!is_null($this->userStar))
            <x-heroicon-s-star class="w-5 h-5 text-yellow-500 group-hover/star:text-yellow-600 transition-all" wire:loading.class="scale-125"  /> {{ __('Starred') }}
        @else
            <x-heroicon-o-star class="w-5 h-5 group-hover/star:text-yellow-500 transition-all" wire:loading.class="scale-125"  /> {{ __('Star') }}
        @endif
    
        @if ($this->starCount > 0)
            <span class="pl-2 inline-flex shrink-0 min-w-4 items-center rounded-full px-2 py-1 bg-gray-50 text-gray-600 ring-gray-500/10 ring-1 ring-inset"
            aria-label="{{ trans_choice(':total user starred this resource|:total users starred this resource', $this->starCount, ['total' => $this->starCount]) }}"
            title="{{ trans_choice(':total user starred this resource|:total users starred this resource', $this->starCount, ['total' => $this->starCount]) }}">
                {{ $this->starCount }}
            </span>
        @endif
    </button>
    
    @can('viewAny', \App\Models\Note::class)
    @if (!is_null($this->userStar))

    <x-dropdown align="right" width="60" state="{ open: $wire.entangle('showPanel') }">
        <x-slot name="trigger">
            <button class="font-semibold text-xs px-2 py-1 flex items-center hover:bg-yellow-50" :class="{'transform rotate-180': open }" title="{{ __('See notes') }}">
                <x-heroicon-o-chevron-down class="w-5 h-5" />
            </button>
    
        </x-slot>
    
        <x-slot name="content">
            <div class="min-w-[20rem] p-4">

                <p class="text-stone-700 mb-4 font-bold">{{ __('Notes') }}</p>

                @forelse ($this->notes as $note)

                    <livewire:note :note="$note" @removed="$refresh" :key="$note->id" />
                    
                @empty
                    
                    @can('create', \App\Models\Note::class)
                        <livewire:take-note @saved="$refresh" :resource="$this->userStar" :description="__('Keep track of what you\'re thinking about. Add a personal note for future retrieval.')" />
                    @else
                        <p class="text-stone-700">{{ __('No thoughts noted down yet.') }}</p>
                    @endcan

                @endforelse
   
        
            </div>
        </x-slot>
    </x-dropdown>
    @endif
    @endcan
    
</div>
