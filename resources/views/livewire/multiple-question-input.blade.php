<div class="w-full">
    <form method="POST" x-ref="js_multiple_question_form" action="{{ route('multiple-questions.store') }}">
        @csrf
        <x-label class="mb-1" for="question" value="{{ __('Enter your question. It can be one or more lines.') }}" />
        <x-textarea wire:model="question" @keydown.ctrl.enter="$refs.js_multiple_question_form.submit()" name="question" id="question" class="min-w-full" rows="3" placeholder="{{ __('Ask a question...') }}">
            {{ $questionQuery ?? null }}
        </x-textarea>

        <input type="hidden" name="strategy" value="{{ $strategy }}">
        
        <div class="flex items-center justify-between">
            <p class="text-sm {{ $exceededMaximumLength ? 'text-red-600 font-bold' : 'text-stone-600' }}">{{ $length }} / {{ config('copilot.limits.question_length') }} {{ trans_choice('character|characters', $length) }}</p>

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
