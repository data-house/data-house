<x-slideover wire:submit="storeField" :title="__('Add field')" :description="__('Add a new field to define what information can be captured in this catalog.')">
    
    <div class="h-6"></div>

    <div>
        <x-label for="title" value="{{ __('Field name') }}" />
        <p class="text-stone-600 text-sm">{{ __('The name of the field as it will appear in the catalog.') }}</p>
        <x-input-error for="editingForm.title" class="mt-2" />
        <x-input id="title" type="text" wire:model="editingForm.title" name="title" class="mt-1 block w-full" autofocus autocomplete="none" />
    </div>

    <div class="mt-4">
        <x-label for="data_type" value="{{ __('Field type') }}" />
        <p class="text-stone-600 text-sm mt-1">{{ __('Select the type of data that will be stored in this field.') }}</p>
        <x-input-error for="editingForm.data_type" class="mt-2" />
        <select id="data_type" wire:model="editingForm.data_type" name="data_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">{{ __('Select a type...') }}</option>
            @foreach($fieldTypes as $type)
                <option value="{{ $type->value }}">
                    {{ __($type->name) }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="mt-4">
        <x-label for="description" value="{{ __('Description') }}" />
        <p class="text-stone-600 text-sm mt-1">{{ __('Add a description to help users understand what this field is for.') }}</p>
        <x-input-error for="editingForm.description" class="mt-2" />
        <x-textarea id="description" wire:model="editingForm.description" name="description" class="mt-1 block w-full" />
    </div>

    @if($editingForm['data_type'])
        <div class="mt-4">
            <x-label for="constraints" value="{{ __('Constraints') }}" />
            <p class="text-stone-600 text-sm mt-1">
                @switch($editingForm['data_type'])
                    @case(\App\CatalogFieldType::TEXT->value)
                    @case(\App\CatalogFieldType::RICH_TEXT->value)
                        {{ __('Optional. Define minimum and maximum length constraints in JSON format. Example: {"min": 10, "max": 1000}') }}
                        @break
                    @case(\App\CatalogFieldType::NUMBER->value)
                        {{ __('Optional. Define minimum and maximum value constraints in JSON format. Example: {"min": 0, "max": 100}') }}
                        @break
                    @case(\App\CatalogFieldType::DATE->value)
                    @case(\App\CatalogFieldType::DATETIME->value)
                        {{ __('Optional. Define date range constraints in JSON format. Example: {"min": "2024-01-01", "max": "2024-12-31"}') }}
                        @break
                    @default
                        {{ __('Optional. Define field-specific constraints in JSON format.') }}
                @endswitch
            </p>
            <x-input-error for="editingForm.constraints" class="mt-2" />
            <x-textarea id="constraints" wire:model="editingForm.constraints" name="constraints" class="mt-1 block w-full font-mono text-sm" placeholder='{"min": 0, "max": 100}' />
        </div>
    @endif

    <x-slot name="actions">
        <x-button type="submit">
            <span wire:loading.remove wire:target="storeField">{{ __('Add field') }}</span>
            <span wire:loading wire:target="storeField">{{ __('Adding...') }}</span>
        </x-button>
    </x-slot>

</x-slideover>
