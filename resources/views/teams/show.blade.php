<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-stone-800 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-0">
            @livewire('teams.update-team-name-form', ['team' => $team])

            @livewire('teams.team-member-manager', ['team' => $team])
            
            @livewire('manage-team-upload-settings', ['team' => $team])
            
            @livewire('manage-team-review-settings', ['team' => $team])

            @if (Gate::check('delete', $team) && ! $team->personal_team)
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.delete-team-form', ['team' => $team])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
