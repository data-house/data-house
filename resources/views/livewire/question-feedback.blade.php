<div>
    @canany(['viewAny', 'create'], \App\Models\QuestionFeedback::class)    
        <div class="flex gap-2">

            <button type="button" x-tooltip.raw="{{ str(__('**Good**. Response quality is acceptable.'))->inlineMarkdown() }}" wire:click="recordPositiveFeedback" wire:loading.attr="disabled" class="group/like text-sm inline-flex gap-1 items-center text-stone-600 px-1 py-0.5 border border-transparent rounded-md  hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <x-heroicon-o-hand-thumb-up class="w-5 h-5 group-hover/like:text-emerald-600 transition-all" wire:target="recordPositiveFeedback" wire:loading.class="animate-pulse -translate-y-2"  />
                {{-- {{ __('Good') }} --}}
                
                <span class="tabular-nums rounded-full p-1 w-6 h-6 bg-stone-100 text-stone-700 text-xs font-mono">
                    {{ $question->likes_count > 100 ? '99+' : $question->likes_count }}
                </span>
            </button>
            {{-- <button type="button" x-tooltip.raw="{{ str(__('**Improvable**. Answer is acceptable.'))->inlineMarkdown() }}" wire:click="recordNeutralFeedback" wire:loading.attr="disabled"  class="group/improvable text-sm inline-flex gap-1 items-center text-stone-600 px-1 py-0.5 border border-transparent rounded-md  hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <x-heroicon-o-hand-raised class="w-5 h-5 group-hover/improvable:text-fuchsia-600 transition-all" wire:target="recordNeutralFeedback" wire:loading.class="animate-pulse -translate-y-2"  />

                {{ __('Improvable') }}

                <span class="tabular-nums rounded-full p-1 w-6 h-6 bg-stone-100 text-stone-700 text-xs font-mono">
                    {{ $question->improvables_count > 100 ? '99+' : $question->improvables_count }}
                </span>
            </button> --}}
            <button type="button" x-tooltip.raw="{{ str(__('**Poor**. Quality of response can be improved.'))->inlineMarkdown() }}" wire:click="recordNegativeFeedback" wire:loading.attr="disabled"  class="group/dislike text-sm inline-flex gap-1 items-center text-stone-600 px-1 py-0.5 border border-transparent rounded-md  hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <x-heroicon-o-hand-thumb-down class="w-5 h-5 group-hover/dislike:text-red-600 transition-all" wire:target="recordNegativeFeedback" wire:loading.class="animate-pulse translate-y-2"  />
                {{-- {{ __('Poor') }} --}}

                <span class="tabular-nums rounded-full p-1 w-6 h-6 bg-stone-100 text-stone-700 text-xs font-mono">
                    {{ $question->dislikes_count > 100 ? '99+' : $question->dislikes_count }}
                </span>
            </button>
        </div>

        <x-dialog-modal :wire:key="$question->uuid" wire:model.live="showingCommentModal" maxWidth="3xl">
            <x-slot name="title">
                {{ __('Could you tell us more about your feedback?') }}
            </x-slot>

            <x-slot name="content">
                @if ($feedback && !$feedback->wasRecentlyCreated)
                    <p class="mb-4 text-sm text-stone-600">{{ __('You originally expressed the feedback on :date.', ['date' => $feedback->created_at]) }}</p>
                @endif
                
                <form action="#" wire:submit="saveComment">
                    <div class="mb-4">
                        <x-label for="reason" value="{{ __('Reason') }}" />
                        <p class="text-sm text-stone-600">{{ __('Please select one reason.') }}</p>

                        <div class="mt-1 flex flex-col gap-2">
                            @foreach (\App\Models\FeedbackReason::cases() as $reason)
                                <x-label class="flex items-start gap-1 rounded hover:bg-stone-100 p-1">
                                    <x-radio class="mt-0.5" name="reason" wire:model="reason" :value="$reason->value" />
                                    <span>
                                        {{ $reason->label() }}
                                        <span class="block font-normal text-xs text-stone-600">{{ $reason->description() }}</span>
                                    </span>
                                </x-label>
                            @endforeach
                        </div>

                        <x-input-error for="reason" class="mt-2" />
                    </div>
            
                    <div>
                        <x-label for="note" value="{{ __('Notes') }} {{ __('(optional)') }}" />
                        <p class="text-sm text-stone-600">{{ __('If the answer is incorrect or partial, please quote the document with the correct answer or the pages where the answer can be found.') }}</p>
                        <x-textarea rows="3" id="note"  wire:model="note" class="mt-1 block w-full"></x-textarea>
                        <x-input-error for="note" class="mt-2" />
                    </div>  
                </form>     
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('showingCommentModal')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-3" wire:click="saveComment" wire:loading.attr="disabled">
                    {{ __('Submit') }}
                </x-button>
            </x-slot>
        </x-dialog-modal>
    @endcanany
</div>
