<div>
    <form wire:submit="save">

        <x-label for="content">
            {{ __('Add a note') }}
        </x-label>
        <p class="text-stone-700 text-sm mb-1">{{ __('Keep track of what you\'re thinking about. Add a personal note for future retrieval.') }}</p>
    
        <x-input-error class="px-4 py-2" for="content" />

        <x-textarea wire:keydown.ctrl.enter="save" wire:model="content" name="content" class="min-w-full" rows="3">
            
        </x-textarea>

        <x-button type="submit" class="mt-2 grow">{{ __('Save') }}</x-button>
    </form>
</div>
