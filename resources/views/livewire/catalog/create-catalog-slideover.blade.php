
<x-slideover wire:submit="storeCatalog" :title="__('Create a catalog')" description="{{ __('Create a new catalog to structure data scattered across documents and projects.') }}" class="max-w-2xl" wire:key="create-catalog-slideover">
    
            <div class="h-6"></div>
    
    
            <div>
                <x-label for="" value="{{ __('Catalog name') }}" />
                <p class="text-stone-600 text-sm">{{ __('Assign your preferred name to easily find this catalog among the others.') }}</p>
                <x-input-error for="editingForm.title" class="mt-2" />
                <x-input id="title" type="text" wire:model="editingForm.title" name="title" class="mt-1 block w-full" autofocus autocomplete="none" />
            </div>

            
            <div class="mt-4">
                <x-label for="description" value="{{ __('Description') }}" />
                <p class="text-stone-600 text-sm mt-1">{{ __('Add a small description about the content or the rationale of this catalog.') }}</p>
                <x-input-error for="description" class="mt-2" />
                <x-textarea id="description" wire:model="editingForm.description" name="description" class="mt-1 block w-full" autocomplete="none" />
            </div>
    
            <x-slot name="actions">
                <x-button  type="submit">
                    <span wire:loading.remove wire:target="storeCatalog">{{ __('Create catalog') }}</span>
                    <span wire:loading wire:target="storeCatalog">{{ __('Creating...') }}</span>
    
                </x-button>
            </x-slot>
    
    
</x-slideover>