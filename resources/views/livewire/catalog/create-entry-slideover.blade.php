<x-slideover wire:submit="save" :title="__('Add entry')" :description="__('Add a new entry to :catalog.', ['catalog' => $catalog->title])">
    
    <div class="h-6"></div>

    {{ $this->form }}


    

    <x-slot name="actions">
        <x-secondary-button type="button" wire:click="saveAndClose">
            <span wire:loading.remove wire:target="saveAndClose">{{ __('Insert and close') }}</span>
            <span wire:loading wire:target="saveAndClose">{{ __('Adding...') }}</span>
        </x-secondary-button>

        <x-button type="submit">
            <span wire:loading.remove wire:target="save">{{ __('Insert entry') }}</span>
            <span wire:loading wire:target="save">{{ __('Adding...') }}</span>
        </x-button>
        

        <x-filament-actions::modals />
    </x-slot>

</x-slideover>
