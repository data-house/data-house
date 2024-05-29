<div class="overflow-y-auto grow  flex flex-col items-stretch">

    <div class="grow flex flex-col space-y-2">
        
        @forelse ($this->notifications as $item)
    
            <div class="px-4 py-2 space-y-2 " wire:key="{{ $item->id }}">
                <p class="text-stone-900 text-sm {{ $item->unread() ? 'font-bold' : '' }}">{{ trans("notification-types." . $item->type) }}</p>

                @includeFirst([$item->type, 'notification.default'], ['notification' => $item])

                <p class="text-xs text-stone-600 flex justify-between">
                    <x-time :value="$item->created_at" />

                    @if ($item->unread())
                        <button wire:click="markRead('{{$item->id}}')">{{ __('Mark read') }}</button>
                    @else
                        <button wire:click="markUnread('{{$item->id}}')">{{ __('Mark unread') }}</button>
                    @endif
                </p>

            </div>

            @unless ($loop->last)
                <div class="mx-4 h-px  bg-stone-200/60"></div>
            @endunless

        @empty
    
            <div class=" grow flex flex-col items-center justify-center">
                <x-heroicon-o-bell class="size-16 text-stone-200" />
    
                <p class="text-stone-700 text-sm">{{ __('Congratulations, you don\'t have any notifications.')}}</p>
            </div>
    
        @endforelse
        
        
    </div>

    <div class="mb-4 px-4 self-end">
        <x-small-button wire:click="markAllAsRead">{{ __('Mark all as read') }}</x-small-button>
    </div>
    
</div>
