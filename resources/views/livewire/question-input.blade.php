<div class="w-full">
    <form method="get" wire:submit="makeQuestion">
        <x-label class="mb-1" for="question" value="{{ __('Enter your question. It can be one or more lines.') }}" />
        <x-textarea wire:keydown.ctrl.enter="makeQuestion" wire:model.live="question" name="question" id="question" class="min-w-full" rows="3" placeholder="{{ __('Ask a question...') }}">
            
        </x-textarea>
        
        <ul class="flex flex-col gap-2">
            @foreach ($this->similarQuestions as $item)
                <li class="flex gap-2 items-center">
                    <a class="contents underline" href="{{ route('questions.show', ['question' => $item->uuid]) }}" target="_blank">
                        <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4 text-stone-600" />
                        <span>{{ str($item->search_match['question'] ?? $item->question)->inlineMarkdown()->toHtmlString() }}</span>
                    </a>
                    <x-copy-clipboard-button :value="$item->question" title="{{ __('Copy question text') }}" class="">
                        {{ __('Copy question') }}
                    </x-copy-clipboard-button>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center justify-between">
            <div class="inline-flex gap-2 divide-x divide-stone-300">
                <p class="text-sm {{ $exceededMaximumLength ? 'text-red-600 font-bold' : 'text-stone-600' }}">{{ $length }} / {{ config('copilot.limits.question_length') }} {{ trans_choice('character|characters', $length) }}</p>

                <p class="pl-2 text-sm {{ $dailyQuestionLimit <= 10 ? 'text-red-600' : 'text-stone-600' }}">{{ trans_choice(':amount question left for today|:amount questions left for today', $dailyQuestionLimit, ['amount' => $dailyQuestionLimit]) }}</p>
            </div>

            <x-button>
                <span wire:loading.remove wire:target="makeQuestion">
                    {{ __('Ask question') }} <span class="font-mono text-xs font-light bg-stone-500 px-1 py-0.5 rounded-sm inline-block">Ctrl+Enter</span>
                </span>
                <span wire:loading wire:target="makeQuestion">
                    {{ __('Elaborating...') }} 
                </span>
                <span wire:loading wire:target="makeQuestion">
                    <svg class="animate-spin ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </x-button>
        </div>

        @error('question') <p class="text-red-600 font-bold">{{ $message }}</p> @enderror
        
        <p class="text-xs text-stone-600">
            {{ __('Answer generation is powered by OpenAI. Please always review answers before use.') }}
        </p>
    </form>
</div>
