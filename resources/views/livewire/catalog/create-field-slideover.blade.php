<x-slideover wire:submit="storeField" :title="__('Create field')" :description="__('Add a new field to your catalog. There are specific fields for various data types, use the best for the information you want to capture. Each catalog entry will have a progressive index and can be connected to documents and projects.')" class="max-w-2xl" wire:key="create-field-slideover">
    
    <div class="h-6"></div>

    <div>
        <x-label for="title" value="{{ __('Name') }}" />
        <p class="text-stone-600 text-sm">{{ __('The name of the field as it will appear in the catalog.') }}</p>
        <x-input-error for="editingForm.title" class="mt-2" />
        <x-input-error for="title" class="mt-2" />
        <x-input id="title" type="text" wire:model="editingForm.title" name="title" class="mt-1 block w-full" autofocus autocomplete="none" />
    </div>

    <div class="mt-4">
        <x-label for="description" value="{{ __('Description') }}" />
        <p class="text-stone-600 text-sm mt-1">{{ __('Add a description to help users understand what this field is for.') }}</p>
        <x-input-error for="editingForm.description" class="mt-2" />
        <x-input-error for="description" class="mt-2" />
        <x-textarea id="description" rows="3" wire:model="editingForm.description" name="description" class="mt-1 block w-full" />
    </div>
    
    <div class="mt-4">
        {{ $this->form }}
    </div>
    

    <x-slot name="actions">
        <x-button type="submit">
            <span wire:loading.remove wire:target="storeField">{{ __('Add field') }}</span>
            <span wire:loading wire:target="storeField">{{ __('Adding...') }}</span>
        </x-button>

        <x-filament-actions::modals />
    </x-slot>

</x-slideover>
