<div class="" {{ $waitForSummaryGeneration && !$this->hasSummary ? 'wire:poll.visible' : '' }} x-data="{selected:1}">
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

                    <div class="flex gap-2 text-sm mt-2">
                        @if ($summary->user)
                            <p class="inline-flex gap-1 items-center"><x-heroicon-m-user class="w-4 h-4 text-stone-500" />{{ $summary->user?->name }}</p>
                        @endif
                        <p class="inline-flex gap-1 items-center">
                            <x-heroicon-m-calendar class="w-4 h-4 text-stone-500" /><x-date :value="$summary->created_at" />
                            @if ($summary->updated_at->notEqualTo($summary->created_at))
                                ({{ __('last updated on :date', ['date' => $summary->updated_at->toDateString()]) }})
                            @endif
                            @can('update', $this->document)
                            @if ($summary->user_id === auth()->user()->getKey() || $summary->isAiGenerated())
                            <x-small-button type="button" wire:click="$dispatch(
                                'openSlideover', { 
                                    component: 'summary-editor', 
                                    arguments: { 
                                        summary: '{{ $summary->getKey() }}'
                                    }
                                })" >
                                <x-heroicon-s-pencil class="text-stone-600 h-4 w-4" />
                                {{ __('Edit')}}
                            </x-small-button>
                            @endif
                            @endcan
                        </p>
                    </div>

                    <x-small-button class="mt-1" x-show="selected != {{ $loop->iteration }}" type="button" @click="selected = {{ $loop->iteration }}">{{ __('Expand') }}</x-small-button>
                </div>
                
            </div>
        @endforeach
    @else
        <div>
            <div class="prose"> 
                {{ __('This document doesn\'t have a summary.', ) }}

                @if ($showCreate)
                    
                

                @can('update', $this->document)
                
                    <div class="mt-2 not-prose">
                        @summary()
                            <livewire:document-summary-button :document="$this->document" />
                        @else                      
                            <x-small-button type="button" wire:click="$dispatch(
                                'openSlideover', { 
                                    component: 'summary-editor', 
                                    arguments: { 
                                        document: '{{ $this->document->ulid }}'
                                    }
                                })" >
                                <x-heroicon-s-pencil class="text-stone-600 h-4 w-4" />
                                {{ __('Write a summary')}}
                            </x-small-button>
                        @endsummary
                    </div>
                @endcan

                @endif

            </div>

        </div>
    @endif
</div>