<div>
    @canany(['viewAny', 'create'], \App\Models\QuestionFeedback::class)    
        <div class="flex gap-2">

            <button type="button" wire:click="like" wire:loading.attr="disabled" class="group text-sm inline-flex gap-1 items-center text-stone-600 px-1 py-0.5 border border-transparent rounded-md  hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <x-heroicon-o-hand-thumb-up class="w-5 h-5 group-hover:text-emerald-600 transition-all" wire:target="like" wire:loading.class="animate-pulse -translate-y-2"  />
                {{ __('Like') }}
                
                <span class="tabular-nums rounded-full p-1 w-6 h-6 bg-stone-100 text-stone-700 text-xs font-mono">
                    {{ $question->likes_count > 100 ? '99+' : $question->likes_count }}
                </span>
            </button>
            <button type="button" wire:click="dislike" wire:loading.attr="disabled"  class="group text-sm inline-flex gap-1 items-center text-stone-600 px-1 py-0.5 border border-transparent rounded-md  hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <x-heroicon-o-hand-thumb-down class="w-5 h-5 group-hover:text-red-600 transition-all" wire:target="dislike" wire:loading.class="animate-pulse translate-y-2"  />
                {{ __('Dislike') }}

                <span class="tabular-nums rounded-full p-1 w-6 h-6 bg-stone-100 text-stone-700 text-xs font-mono">
                    {{ $question->dislikes_count > 100 ? '99+' : $question->dislikes_count }}
                </span>
            </button>
        </div>

        <x-dialog-modal :wire:key="$question->uuid" wire:model="showingDislikeModal">
            <x-slot name="title">
                {{ __('Could you tell us why the answer is unsatisfactory?') }}
            </x-slot>

            <x-slot name="content">
                @if ($feedback && !$feedback->wasRecentlyCreated)
                    <p class="mb-4 text-sm text-stone-600">{{ __('You originally expressed the feedback on :date.', ['date' => $feedback->created_at]) }}</p>
                @endif
                
                <form action="#" wire:submit.prevent="saveDislikeComment">
                    <div class="mb-4">
                        <x-label for="reason" value="{{ __('Reason') }}" />
                        <p class="text-sm text-stone-600">{{ __('Please select one reason.') }}</p>

                        <div class="mt-1 grid grid-cols-4 gap-2">
                            @foreach (\App\Models\FeedbackReason::cases() as $reason)
                                <x-label class="flex items-start gap-1">
                                    <x-radio class="mt-0.5" name="reason" wire:model.defer="feedback.reason" :value="$reason->value" />
                                    {{ $reason->label() }}
                                </x-label>
                            @endforeach
                        </div>

                        <x-input-error for="feedback.reason" class="mt-2" />
                    </div>
            
                    <div>
                        <x-label for="note" value="{{ __('Notes') }}" />
                        <p class="text-sm text-stone-600">{{ __('If the answer is incorrect or partial, please quote the document with the correct answer or the pages where the answer can be found.') }}</p>
                        <x-textarea rows="3" id="note"  wire:model.defer="feedback.note" class="mt-1 block w-full"></x-textarea>
                        <x-input-error for="feedback.note" class="mt-2" />
                    </div>  
                </form>     
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('showingDislikeModal')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-3" wire:click="saveDislikeComment" wire:loading.attr="disabled">
                    {{ __('Submit') }}
                </x-button>
            </x-slot>
        </x-dialog-modal>
    @endcanany
</div>
