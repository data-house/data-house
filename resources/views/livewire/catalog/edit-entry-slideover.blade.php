<x-slideover wire:submit="save" :title="__('Edit entry')" :description="__('Edit entry :entry to :catalog.', ['entry' => $entry->entry_index, 'catalog' => $catalog->title])">
    
    <div class="h-6"></div>

    @if ($entry->trashed())
        <div class="py-2 px-2 flex gap-1 items-center min-w-0 mb-4 bg-stone-100 rounded">
            <x-heroicon-c-trash class="size-4 text-current" />

            <p class="ml-3 font-medium text-sm">{{ __('You are looking at a trashed entry.') }}

                @can('restore', $entry)
                    <x-small-button wire:click="restoreEntry">
                        <span wire:loading.remove wire:target="restoreEntry">{{ __('Restore') }}</span>
                        <span wire:loading wire:target="restoreEntry">{{ __('Restoring...') }}</span>
                    </x-small-button>
                @endcan

            </p>
        </div>            
    @endif

    {{ $this->form }}


    <div class="h-4"></div>

    <x-slot name="actions">

        <div class="grow w-full flex items-end justify-between">

            @if ($entry->trashed())
                @can('forceDelete', $entry)
                    <x-danger-button type="button" wire:click="forceDelete">
                        <span wire:loading.remove wire:target="forceDelete">{{ __('Permanently delete') }}</span>
                        <span wire:loading wire:target="forceDelete">{{ __('Pruning...') }}</span>
                    </x-danger-button>
                @endcan
            @else
                @can('delete', $entry)
                    <x-danger-button type="button" wire:click="trash">
                        <span wire:loading.remove wire:target="trash">{{ __('Delete') }}</span>
                        <span wire:loading wire:target="trash">{{ __('Trashing...') }}</span>
                    </x-danger-button>
                @endcan

                <x-button type="submit">
                    <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-button>
            @endif

        </div>

        

        <x-filament-actions::modals />
    </x-slot>

</x-slideover>
