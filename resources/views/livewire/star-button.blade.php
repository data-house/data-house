<x-secondary-button
    type="button"
    wire:click="toggle"
    class="group/star focus:ring-yellow-500 hover:bg-yellow-50 hover:border-yellow-400" >

    @if (!is_null($this->userStar))
        <x-heroicon-s-star class="w-5 h-5 text-yellow-500 group-hover/star:text-yellow-600 transition-all" wire:loading.class="motion-safe:animate-bounce"  /> {{ __('Starred') }}
    @else
        <x-heroicon-o-star class="w-5 h-5 group-hover/star:text-yellow-500 transition-all" wire:loading.class="motion-safe:animate-bounce"  /> {{ __('Star') }}
    @endif

    @if ($this->starCount > 0)
        <span class="inline-flex shrink-0 min-w-4 items-center rounded-full px-2 py-1 bg-gray-50 text-gray-600 ring-gray-500/10 ring-1 ring-inset"
        aria-label="{{ trans_choice(':total user starred this resource|:total users starred this resource', $this->starCount, ['total' => $this->starCount]) }}"
        title="{{ trans_choice(':total user starred this resource|:total users starred this resource', $this->starCount, ['total' => $this->starCount]) }}">
            {{ $this->starCount }}
        </span>
    @endif
</x-secondary-button>
