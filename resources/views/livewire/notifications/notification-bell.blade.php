<button
    wire:poll.10s.visible="pollingBeat"
    class="
        relative focus:border-stone-300 
        h-9 inline-flex items-center px-2 py-2 border border-transparent text-sm leading-4 font-medium rounded-md  hover:text-stone-800 hover:bg-stone-200 focus:outline-none focus:bg-stone-200 active:bg-stone-200 transition duration-150 ease-in-out
        @if ($this->hasUnreadNotifications)  bg-orange-50 text-orange-800 ring-orange-600/20 ring-1 ring-inset @else text-stone-600 @endif
    "
    x-tooltip.raw="{{ $this->hasUnreadNotifications ? __('You have unread notifications') : ($this->snoozed ? __('Notification are snoozed') : __('Notifications')) }}"
    >
    
    @if ($this->snoozed)
        <x-heroicon-o-bell-snooze class="size-5" />
    @else 
        @if ($this->hasUnreadNotifications)
            <span wire:transition.origin.right class="inline-flex items-center  px-2 text-xs font-mono font-medium ">
                {{ \Illuminate\Support\Number::abbreviate($this->unreadNotificationsCount) }}
            </span>
            <x-heroicon-o-bell-alert class="size-5 " />
        @else
            <x-heroicon-o-bell class="size-5" />
        @endif
    @endif
    

</button>