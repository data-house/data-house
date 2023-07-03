<div class="w-full">
    <form method="get" wire:submit.prevent="makeQuestion">
        <x-textarea wire:model="question" name="question" id="question" class="min-w-full" rows="3" placeholder="{{ __('Ask a question...') }}">
            {{ $questionQuery ?? null }}
        </x-textarea>
        
        <div class="flex items-center justify-between">
            <p class="text-sm {{ $exceededMaximumLength ? 'text-red-600 font-bold' : 'text-stone-600' }}">{{ $length }} / {{ config('copilot.limits.question_length') }} {{ trans_choice('character|characters', $length) }}</p>

            <x-button>
                <span wire:loading.remove wire:target="makeQuestion">
                    {{ __('Send') }}
                </span>
                <span wire:loading wire:target="makeQuestion">
                    {{ __('Sending...') }}
                </span>
            </x-button>
        </div>

        @error('question') <p class="text-red-600 font-bold">{{ $message }}</p> @enderror
        
    </form>
    <p class="text-xs text-stone-600">
        {{ __('Answer generation is powered by OpenAI. Please always review answers before use.') }}
    </p>
</div>
