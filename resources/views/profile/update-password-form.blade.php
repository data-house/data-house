<x-form-section submit="updatePassword">
    <x-slot name="title">
        {{ __('Update Password') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Ensure your account is using a long, random password to stay secure.') }}
    </x-slot>

    <x-slot name="form">

        @if ($this->passwordExpiresOn)
            <div class="col-span-6 py-2 -mx-4 sm:-mx-6 px-4 sm:px-6 bg-yellow-100 space-y-1">

                @unless ($this->passwordExpiresOn->gt(now()))
                    <p class="text-sm text-stone-700 font-medium">{{ __('Your password expired on :date (:remaining), please update your password!', ['date' => $this->passwordExpiresOn->locale(app()->currentLocale())->setTimezone(config('app.timezone'))->toDateString(), 'remaining' => $this->passwordExpiresOn->longRelativeDiffForHumans()]) }}</p>
                @endunless

                <p class="text-sm text-stone-700 font-medium">{{ __('As a policy you are asked to update your password every :days days.', ['days' => (int) config('auth.password_validation.expire_after_days')]) }}</p>

                @if ($this->passwordExpiresOn->gt(now()))
                    <p class="text-sm text-stone-700">{{ __('You will be asked to update your password on :date (:remaining)', ['date' => $this->passwordExpiresOn->locale(app()->currentLocale())->setTimezone(config('app.timezone'))->toDateString(), 'remaining' => $this->passwordExpiresOn->longAbsoluteDiffForHumans()]) }}</p>
                @endif
            </div>
        @endif

        <div class="col-span-6 sm:col-span-4">
            <p class="text-sm text-stone-700">{{ __('Last changed on:') }} <x-date :value="$this->lastPasswordUpdate" /></p>
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="current_password" value="{{ __('Current Password') }}" />
            <x-input id="current_password" type="password" class="mt-1 block w-full" wire:model="state.current_password" autocomplete="current-password" />
            <x-input-error for="current_password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password" value="{{ __('New Password') }}" />
            <ul class="text-sm text-gray-600">
                <li>{{ __('Your password must be at least :min_length characters long.', ['min_length' => config('auth.password_validation.minimum_length', 12)]) }}</li>
                <li>{{ __('Your password must include a mix of uppercase, lowercase, numbers, and special characters (e.g. ! # ?).') }}</li>
                <li>{{ __('Avoid using your email address or any part of it in your password.') }}</li>
            </ul>
            <x-input id="password" type="password" class="mt-1 block w-full" wire:model="state.password" autocomplete="new-password" />
            <x-input-error for="password" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
            <x-input id="password_confirmation" type="password" class="mt-1 block w-full" wire:model="state.password_confirmation" autocomplete="new-password" />
            <x-input-error for="password_confirmation" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
