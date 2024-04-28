<div>
    <form wire:submit="save">

        <x-label for="content">
            {{ __('Add a note') }}
        </x-label>

        @if ($description)
            <p class="text-stone-700 text-sm mb-1">{{ $description }}</p>
        @endif
    
        <x-input-error class="px-4 py-2" for="content" />

        <x-textarea wire:keydown.ctrl.enter="save" wire:model="content" name="content" class="min-w-full" rows="3">
            
        </x-textarea>

        <x-button type="submit" class="mt-2 grow">
            <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
        </x-button>
    </form>
</div>
