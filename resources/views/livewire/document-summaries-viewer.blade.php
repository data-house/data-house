<div class="" {{ $waitForSummaryGeneration && !$this->hasSummary() ? 'wire:poll.visible' : '' }}>
    @if ($this->hasSummary)
        <div class="prose">
            {{ $this->latestSummary }}
        </div>
        @if ($this->latestSummary->isAiGenerated())
            <p class="mt-2 py-1 text-sm text-lime-700 flex items-center gap-1">
                <x-heroicon-s-sparkles class="text-lime-500 h-6 w-6" />
                {{ __('This summary is automatically generated.') }}
            </p>
        @endif
    @else
        <div>
            <div class="prose">
                {{ __('This document doesn\'t have an abstract.', ) }}

                @summary()
                    <div class="mt-2 not-prose">
                        <livewire:document-summary-button :document="$this->document" />
                        
                    </div>
                @else

                    {{ str(__('[Write a summary for the document](:url)', ['url' => route('documents.edit', $document)]))->markdown()->toHtmlString() }}
                    
                @endsummary
            </div>

        </div>
    @endif
</div>