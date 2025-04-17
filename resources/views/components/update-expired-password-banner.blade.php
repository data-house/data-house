@if ($expired)
    

<div class="bg-gradient-to-b from-red-800 to-10% to-red-700 rounded-t text-white flex flex-col items-start gap-2 py-2 px-4 sm:px-6 lg:px-8">
    <p class="font-medium">{{ __('As a policy you are asked to update your password every :days days.', ['days' => (int) config('auth.password_validation.expire_after_days')]) }}</p>
    <p class="text-sm">{{ __('Your password expired on :date (:remaining), please update your password!', ['date' => $passwordExpiresOn->locale(app()->currentLocale())->setTimezone(config('app.timezone'))->toDateString(), 'remaining' => $passwordExpiresOn->longRelativeDiffForHumans()]) }}</p>
    <x-button-link :href="route('profile.show')">{{ __('Update your password') }}</x-button-link>
</div>
@endif