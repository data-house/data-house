<div class="w-full">
    <form method="POST" x-ref="js_multiple_question_form" action="{{ route('multiple-questions.store') }}">
        @csrf
        @if ($guided)
            <x-label class="mb-1" for="question" value="{{ __('Complete the question') }}" />
            <div class="mb-2 p-3 border bg-white border-stone-300 focus-within:border-lime-500 focus-within:ring-lime-500 rounded-md shadow-sm min-w-full min-h-20 h-20">
                <p class=" flex gap-1">
                    <span>{{ __('What are the main') }}</span>
                    <input class="border-b border-x-0 border-t-0 p-0 w-96 focus:border-x-0 focus:border-t-0 focus:border-lime-500 focus:border-2 focus:ring-0" autocomplete="none" type="text" wire:model="question" @keydown.ctrl.enter="$refs.js_multiple_question_form.submit()" name="question" id="question" value="{{ $questionQuery ?? null }}">
                    <span>{{ __('in the reports?') }}</span>
                </p>
            </div>
        @else
            <x-label class="mb-1" for="question" value="{{ __('Enter your question (it can be one or more lines)') }}" />
            <x-textarea wire:model="question" @keydown.ctrl.enter="$refs.js_multiple_question_form.submit()" name="question" id="question" class="mb-1 min-w-full min-h-20" rows="3" placeholder="{{ __('Ask a question...') }}">
                {{ $questionQuery ?? null }}
            </x-textarea>
        @endif
        <div class="">
            @if ($guided)
                <button type="button" wire:click="$toggle('guided')" class="group inline-flex gap-2 items-center bg-white/90 px-2 py-1 hover:bg-blue-100 focus:bg-blue-100 border border-blue-400 rounded-md font-semibold text-xs active:text-white  active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <span class="inline-flex gap-1 items-center">
                        <x-heroicon-s-cube-transparent class="w-5 h-5 text-stone-400 group-hover:text-blue-600 group-focus:text-blue-600" />
                        <x-heroicon-s-arrow-small-left class="w-3 h-3 text-stone-600" />
                        <x-heroicon-s-cube class="w-5 h-5 text-blue-600 group-hover:text-stone-400 group-focus:text-stone-400" />
                    </span>
                    {{ __('Switch back to open question mode') }}
                </button>
            @else
                <button type="button" wire:click="$toggle('guided')" class="group inline-flex gap-2 items-center bg-white/90 px-2 py-1 hover:bg-blue-100 focus:bg-blue-100 border border-blue-400 rounded-md font-semibold text-xs active:text-white  active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <span class="inline-flex gap-1 items-center">
                        <x-heroicon-s-cube-transparent class="w-5 h-5 text-blue-600 group-hover:text-stone-400 group-focus:text-stone-400" />
                        <x-heroicon-s-arrow-small-right class="w-3 h-3 text-stone-600" />
                        <x-heroicon-s-cube class="w-5 h-5 text-stone-400 group-hover:text-blue-600 group-focus:text-blue-600" />
                    </span>
                    {{ __('Switch to guided question mode') }}
                </button>
            @endif
        </div>

        <input type="hidden" name="strategy" value="{{ $strategy }}">
        
        <input type="hidden" name="guidance" value="{{ $guided }}">
        
        <input type="hidden" name="collection" value="{{ $collection?->getKey() }}">
        
        <div class="flex items-center justify-between">
            <div class="inline-flex gap-2 divide-x divide-stone-300">
                <p class="text-sm {{ $exceededMaximumLength ? 'text-red-600 font-bold' : 'text-stone-600' }}">{{ $length }} / {{ config('copilot.limits.question_length') }} {{ trans_choice('character|characters', $length) }}</p>

                <p class="pl-2 text-sm {{ $dailyQuestionLimit <= 10 ? 'text-red-600' : 'text-stone-600' }}">{{ trans_choice(':amount question left for today|:amount questions left for today', $dailyQuestionLimit, ['amount' => $dailyQuestionLimit]) }}</p>
            </div>

            <x-button type="submit">
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
