<x-slideover wire:submit="saveAndClose" :title="__('Add entry')" :description="__('Add a new entry to :catalog.', ['catalog' => $catalog->title])">
    
    <div class="h-6"></div>

    {{ $this->form }}


    <div class="h-4"></div>

    <x-slot name="actions">
        <x-button type="submit">
            <span wire:loading.remove wire:target="saveAndClose">{{ __('Insert entry') }}</span>
            <span wire:loading wire:target="saveAndClose">{{ __('Adding...') }}</span>
        </x-button>
        

        <x-filament-actions::modals />
    </x-slot>

</x-slideover>
