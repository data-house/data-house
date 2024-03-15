<div class="" {{ $waitForSummaryGeneration && !$this->hasSummary() ? 'wire:poll.visible' : '' }} x-data="{selected:1}">
    @if ($this->hasSummary)
        @foreach ($this->summaries as $summary)
            <div class="flex gap-2 mb-4">
                <div class="flex items-center flex-col gap-4">
                    <span class="text-xs uppercase font-mono inline-block px-2 py-1 bg-white border border-stone-700/10 rounded-sm shadow">{{ $summary->language->value }}</span>

                    <div @class(['w-px h-full', 'bg-lime-700/20' => $summary->isAiGenerated(), 'bg-stone-600/20' => !$summary->isAiGenerated()]) ></div>
                    
                </div>
                <div >
                    @if ($summary->isAiGenerated())
                        <p class="text-sm text-lime-700 flex items-center gap-1">
                            <x-heroicon-s-sparkles class="text-lime-500 h-6 w-6 line-clamp-2" />
                            {{ __('This summary is automatically generated.') }}
                        </p>
                    @endif

                    <div class="prose" x-bind:class="{'line-clamp-2': selected != {{ $loop->iteration }}}" x-show="selected == {{ $loop->iteration }}" x-collapse.min.50px>
                        {{ $summary }}
                    </div>

                    <x-small-button class="mt-1" x-show="selected != {{ $loop->iteration }}" type="button" @click="selected = {{ $loop->iteration }}">{{ __('Expand') }}</x-small-button>
                </div>
                
            </div>
        @endforeach
    @else
        <div>
            <div class="prose"> 
                {{ __('This document doesn\'t have an abstract.', ) }}

                @can('update', $this->document)
                    
                    @summary()
                        <div class="mt-2 not-prose">
                            <livewire:document-summary-button :document="$this->document" />
                            
                        </div>
                    @else

                        {{ str(__('[Write a summary for the document](:url)', ['url' => route('documents.edit', $this->document)]))->markdown()->toHtmlString() }}
                        
                    @endsummary
                @endcan

            </div>

        </div>
    @endif
</div>