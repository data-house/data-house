<button
    wire:poll.10s.visible="pollingBeat"
    class="
        relative flex items-center border-2 p-1 border-transparent rounded-full focus:outline-none focus:border-stone-300 transition
        @if ($this->hasUnreadNotifications)  px-2 py-1 bg-orange-50 text-orange-800 ring-orange-600/20 ring-1 ring-inset @endif
    "
>
    
    @if ($this->hasUnreadNotifications)
        <span wire:transition.origin.right class="inline-flex items-center  px-2 text-xs font-mono font-medium ">
            {{ \Illuminate\Support\Number::abbreviate($this->unreadNotificationsCount) }}
        </span>
    
        <x-heroicon-o-bell-alert class="size-5 " />
    @else
        <x-heroicon-o-bell class="size-5" />
    @endif

</button>