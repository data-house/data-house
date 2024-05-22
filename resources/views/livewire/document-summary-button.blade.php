<div {{ $this->generatingSummary && !$this->hasSummary ? 'wire:poll.visible' : ''}}>
@if(!$this->hasSummary)
    <x-input-error for="generate_summary" class="mb-2" />

    <x-small-button type="button" wire:click="generateSummary" :disabled="!$this->canGenerateSummary" class="text-lime-700 flex items-center gap-1 border border-lime-400 bg-lime-50 hover:bg-lime-100 hover:border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 focus:bg-lime-100 focus:border-lime-500">
        @unless ($this->generatingSummary)
            <x-heroicon-s-sparkles class="text-lime-500 h-4 w-4" />
            {{ __('Generate a summary')}}
        @else
            <svg class="animate-spin h-4 w-4 text-lime-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('Writing a summary for you...')}}
        @endunless
    </x-small-button>

    @if ($this->summaryGenerationFailed)
        @support()
            @if (\App\HelpAndSupport\Support::hasTicketing())
                <p class="text-red-600">{{ str(__('A summary could not be generated at the moment. Please try later or [contact the support](:support_url).', ['support_url' => \App\HelpAndSupport\Support::buildSupportTicketLink()]))->inlineMarkdown()->toHtmlString() }}</p>
            @endif
        @else
            <p class="text-red-600">{{ __('A summary could not be generated at the moment. Please try again later.') }}</p>
        @endsupport
    @endif

    @if ($this->canGenerateSummary)
        @unless ($this->generatingSummary)
            <p class="text-xs text-stone-700">
                {{ __('A summary is automatically generated in :languages.', ['languages' => $this->summaryLanguages->join(', ', ' and ') ]) }}
                {{ __('Summary generation is currently limited to :limit documents per team. Your team has :remaining documents remaining.', [
                    'limit' => $this->summaryLimit,
                    'remaining' => $this->remainingSummaryLimit,
                ]) }}
            </p>
        @else
            <p class="text-xs text-stone-700">
                {{ __('Summary generation in progress. Generation can take some time depending on the document\'s length.') }}
            </p>
        @endunless

    @else
        <p class="text-sm text-stone-700 flex gap-1 max-w-sm">
            <x-heroicon-c-exclamation-triangle class="mt-0.5 w-4 h-4 text-yellow-500 shrink-0" />
            {{ __('Summary generation is not available if the document contains only images or no selectable text.') }}
        </p>
    @endif
@endif
</div>