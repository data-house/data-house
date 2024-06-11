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
                        <h4 class="font-bold mb-3">{{ __('Review by :team', ['team' => $review->team->name]) }}</h4>

                        <div class="mb-2 inline-flex items-center gap-1 text-stone-900 px-1 py-0.5 border border-transparent rounded-md  bg-stone-200">
                            <x-dynamic-component :component="$review->statusIcon()" class="w-5 h-5 text-stone-700"  />
                                    
                            {{ $review->statusLabel() }}
                        </div>

                        <div>
                            @foreach ($review->feedbacks->map->user as $reviewer)
                                <p class="mb-2">
                                    <x-user :user="$reviewer" />
                                </p>
                            @endforeach
                        </div>

                        @if ($review->remarks)
                            <div class="prose prose-sm">
                                {{ str($review->remarks)->markdown()->toHtmlString() }}
                            </div>
                        @endif

                    </div>

                @endforeach

            </div>
    
        </x-slideover>
    @endunless
</div>