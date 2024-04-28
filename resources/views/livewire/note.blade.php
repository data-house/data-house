<div class="">
    @if ($note)
        @if ($isEditing)
            <form wire:submit="save">

                <x-label for="content">
                    {{ __('Edit note') }}
                </x-label>
            
                <x-input-error class="px-4 py-2" for="content" />
        
                <x-textarea wire:keydown.ctrl.enter="save" wire:model="content" name="content" class="min-w-full" rows="3">
                    
                </x-textarea>
        
                <div>
                    <x-button type="submit" class="mt-2 grow">
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                        <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                    </x-button>
                    <button type="button" class="underline" wire:click="toggleEditMode">
                        <span wire:loading wire:target="toggleEditMode">{{ __('Closing...') }}</span>
                        <span wire:loading.remove wire:target="toggleEditMode">{{ __('Cancel') }}</span>
                    </button>
                </div>
            </form>        
        @else
            <div class="prose">
                {{ $note }}
            </div>
            <div class="flex text-xs mt-1 justify-between">
                <div class="inline-flex gap-2">
                    <p class="inline-flex gap-1"><x-heroicon-m-user class="w-4 h-4 text-stone-500" />{{ $note->user?->name }}</p>
                    <p class="inline-flex gap-1">
                        <x-heroicon-m-calendar class="w-4 h-4 text-stone-500" /><x-date :value="$note->created_at" />
                        @if ($note->updated_at->notEqualTo($note->created_at))
                            ({{ __('last updated on :date', ['date' => $note->updated_at->toDateString()]) }})
                        @endif
                    </p>
                </div>

                <div class="inline-flex gap-2">
                    @can('update', $note)
                        <button type="button" class="underline" wire:click="toggleEditMode">
                            <span wire:loading wire:target="toggleEditMode">{{ __('Loading...') }}</span>
                            <span wire:loading.remove wire:target="toggleEditMode">{{ __('Edit') }}</span>
                        </button>
                    @endcan
                    @can('delete', $note)
                        <button type="button" class="underline" wire:click="remove">
                            <span wire:loading wire:target="remove">{{ __('Deleting...') }}</span>
                            <span wire:loading.remove wire:target="remove">{{ __('Delete') }}</span>
                        </button>
                    @endcan
                </div>
            </div>
        @endif
    @elseif($trashed)
        <div class="text-stone-700">
            {{ __('Note removed.') }}
        </div>
    @endif

</div>
