<div class="h-full grow">
    @unless ($this->hasReviews)
        <x-slideover wire:submit="requestReview" :title="__('Request a review')" description="{{ __('Request an expert review of the answer. Monitor the review process and the outcome.')}}">
    
            <div class="h-6"></div>
    
            <x-action-message class="mb-3" on="review-requested">
                <x-banner message="{{ __('Request to review the answer sent.') }}" />
            </x-action-message>
    
            <div>
                <x-label for="" value="{{ __('Select reviewers') }}" />
                <p class="text-stone-600 text-sm">{{ __('Select one or more teams to ask the review of the answer.') }}</p>
                <x-input-error for="editingForm.teams" class="mt-2" />
                {{-- <x-textarea id="description" rows="12" type="text" wire:model.live.debounce.2000ms="editingForm.text" name="description" class="mt-1 block w-full max-w-prose" autocomplete="abstract"></x-textarea> --}}
                @forelse ($this->reviewerTeams as $team)
    
                    <label class="flex items-center px-4 py-3">
                        <x-checkbox wire:model="editingForm.teams" :value="$team->getKey()"/>
                        <span class="ml-2 text-sm text-stone-900">{{ $team->name }}</span>
                    </label>
    
                @empty
                    <p class="text-stone-600">{{ __('No teams available for reviewing questions.') }}</p>
                @endforelse
            </div>
    
            <x-slot name="actions">
                <x-button  type="submit">
                    <span wire:loading.remove wire:target="requestReview">{{ __('Submit review request') }}</span>
                    <span wire:loading wire:target="requestReview">{{ __('Submitting...') }}</span>
    
                </x-button>
            </x-slot>
    
    
        </x-slideover>
    @else
        <x-slideover :title="__('Question review')" description="{{ __('Monitor the expert review process and the outcome.')}}">
    
            <div class="h-6"></div>
    
            <x-action-message class="mb-3" on="review-requested">
                <x-banner message="{{ __('Request to review the answer sent.') }}" />
            </x-action-message>

            <div class="space-y-6">
                
                @foreach ($this->reviews as $review)
                    <div class="">
                        <h4 class="font-bold mb-2">{{ __('Review by :team', ['team' => $review->team->name]) }}</h4>

                        <p class="mb-2 flex items-center gap-1 text-stone-600">
                            <x-dynamic-component :component="$review->statusIcon()" class="w-5 h-5 group-hover/reviewers:text-stone-800 transition-all"  />
                                    
                            {{ $review->statusLabel() }}
                        </p>
                        <p class="mb-2">
                            @foreach ($review->assignees as $reviewer)
                                {{ $reviewer->name }}
                            @endforeach
                        </p>

                        @if ($review->remarks)
                            <p class="prose">
                                {{ str($review->remarks)->inlineMarkdown() }}
                            </p>
                        @endif

                    </div>

                @endforeach

            </div>
    
        </x-slideover>
    @endunless
</div>