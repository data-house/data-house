<div>
    <x-small-button type="button" wire:click="generateSummary">
        @unless ($generatingSummary)
            <x-heroicon-s-sparkles class="text-lime-500 h-4 w-4" />
            {{ __('Generate a summary for the document')}}
        @else
            <svg class="animate-spin h-4 w-4 text-lime-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('Writing a summary for you...')}}
        @endunless
    </x-small-button>

    @unless ($generatingSummary)
        <p class="text-sm text-stone-700">
            {{ __('A summary is automatically generated in :languages.', ['languages' => 'English']) }}
            {{ __('Summary generation is currently limited to 50 documents per team. Your team has 50 documents remaining.') }}
        </p>
    @else
        <p class="text-sm text-stone-700">
            {{ __('Summary generation in progress. Generation can take some time depending on the document\'s length.') }}
        </p>
    @endunless
</div>
