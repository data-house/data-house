<x-slideover wire:submit="save" :title="$this->summaryId ? __('Edit summary') : __('Write a summary')" description="{{ __('Please provide a brief overview of the main points or key information that captures the essence of the content or topic. Aim for clarity and brevity, ideally between 2-4 paragraphs.')}}">
    
    <div class="h-6"></div>

    <x-action-message class="me-3" on="summary-saved">
        <x-banner message="{{ __('Summary saved.') }}" />
    </x-action-message>
        
    <div>
        <x-label for="description" value="{{ __('Summary text') }}" />
        <x-input-error for="editingForm.text" class="mt-2" />
        <x-textarea id="description" rows="12" type="text" wire:model.live.debounce.2000ms="editingForm.text" name="description" class="mt-1 block w-full max-w-prose" autocomplete="abstract">{{ $this->summary?->text }}</x-textarea>
    </div>
        
    <div class="mt-4">
        <x-label for="language" value="{{ __('Language') }}" />
        
        <x-input-error for="editingForm.language" class="mt-2" />
        
        <div class="flex justify-between items-center mt-1">
            <div class="text-sm text-stone-700">
                @if ($suggestedLanguage)
                    <div class="inline-flex items-center gap-1">
                        <span class="uppercase font-mono inline-block px-2 py-1 bg-white border border-stone-700/10 rounded-sm shadow">{{ $suggestedLanguage }}</span>
                
                        {{ $this->localizedSuggestedLanguage }}
                    </div>
                @else
                    {{ __('Please type some words, language will be recognized as you type.') }}
                @endif
            </div>
    
            <div class="flex items-center gap-2">
                <span class="text-xs ">
                    @if ($suggestedLanguage)
                        {{ __('Not :language?', ['language' => $this->localizedSuggestedLanguage])}}
                    @endif
                </span>

                <select name="language" id="language" wire:model="editingForm.language" class="border-stone-300 focus:border-lime-500 focus:ring-lime-500 rounded-md shadow-sm">
                    <option value="">{{ __('Select language') }}</option>
                    @foreach ($this->availableLanguages as $lang => $label)
                        <option value="{{ $lang }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        
    </div>

    <x-slot name="actions">
        <x-button type="submit">
            <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
            <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
        </x-button>
    </x-slot>

</x-slideover>
