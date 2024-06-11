<div>
    @if (Gate::check('addTeamMember', $team))
    <x-section-border />
    
    <div class="mt-10 sm:mt-0">
        <!-- Upload settings -->
        <x-form-section submit="updateReviewSettings">
            <x-slot name="title">
                {{ __('Review') }}
            </x-slot>
    
            <x-slot name="description">
                {{ __('Configure team members as reviewer of generated answers by Artificial Intelligence.') }}
            </x-slot>
    
            <x-slot name="form">

                <div class="col-span-6 sm:col-span-4">

                    <x-input-error class="px-4 py-2" for="reviewSettingsForm.questionReview" />

                    <x-switch wire:model="reviewSettingsForm.questionReview">
                        {{ __('Question/Answer reviewer') }}
            
                        <x-slot name="description">{{ __('Team members can be assigned to review generated answers.') }}</x-slot>
                    </x-switch>
                </div>
    
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="assignableUserRoles" value="{{ __('Eligible reviewers') }}" />
                    <div class="max-w-xl text-sm text-stone-600">
                        {{ __('Members with the following roles can be assigned as reviewer.') }}
                    </div>
                    
                    <div class="prose">
                        <ul>
                            @foreach ($roles as $item)
                                <li>
                                    {{ $item->label() }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
    
            </x-slot>
    
            <x-slot name="actions">
                <x-action-message class="mr-3" on="saved">
                    {{ __('Configuration saved.') }}
                </x-action-message>
    
                <x-button>
                    {{ __('Save') }}
                </x-button>
            </x-slot>
        </x-form-section>
    </div>
    @endif
</div>