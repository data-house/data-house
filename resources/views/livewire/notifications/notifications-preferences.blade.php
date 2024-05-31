<x-slideover wire:submit="save" :title="__('Notification preferences')" description="{{ __('Customise your notification preferences. You can configure how you want to receive a summary of what has happened in the Digital Library or snooze notifications.')}}">
    
    <div class="h-6"></div>

    <x-action-message class="mb-3" on="notifications-saved">
        <x-banner message="{{ __('Preferences updated.') }}" />
    </x-action-message>

    @if ($snooze)
        <div class="bg-yellow-100 flex gap-2 items-start p-2 rounded mb-4">
            <x-heroicon-o-bell-snooze class="size-6 shrink-0" />
            <p class="text-sm text-stone-600">{{ __('Notifications are snoozed. You will not be notified about activities and events. Security notifications like password reset are always active.') }}</p>
        </div>
    @endif

    <div class="">

        <x-switch wire:model="snooze">
            {{ __('Snooze') }}

            <x-slot name="description">{{ __('Need to showcase documents. Snooze notifications so you don\'t get interrupted.') }}</x-slot>
        </x-switch>

    </div>
        
    <div class="mt-6">
        <h4 class="font-medium text-stone-900">{{ __('Activity Summary') }}</h4>
        <p class="mt-1 text-sm text-stone-600 mb-4">
            {{ __('Receive a summary of what happened in the digital library.') }}
        </p>

        <x-switch wire:model="activitySummaryEnabled">
            {{ __('Send me an activity summary') }}

            <x-slot name="description">
                {{ __('A recap of what happened in the previous week. Sent usually on Monday at 3pm UTC.') }}
            </x-slot>
        </x-switch>

    </div>

</x-slideover>