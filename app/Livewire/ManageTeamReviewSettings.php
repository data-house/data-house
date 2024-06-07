<?php

namespace App\Livewire;

use Livewire\Component;
use App\Data\ReviewSettings;

class ManageTeamReviewSettings extends Component
{
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The "review settings" form state.
     *
     * @var array
     */
    public $reviewSettingsForm = [
        'questionReview' => false,
        'assignableUserRoles' => [],
    ];


    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        if($team->settings?->review){
            $this->reviewSettingsForm = $team->settings->review->toArray();
        }
    }


    /**
     * Save the new settings.
     *
     * @return void
     */
    public function updateReviewSettings()
    {
        $this->resetErrorBag();

        $this->validate([
            'reviewSettingsForm.questionReview' => 'bool',
        ]);

        $this->team->settings->review = ReviewSettings::from($this->reviewSettingsForm);

        $this->team->save();

        $this->dispatch('saved');
    }


    public function render()
    {
        return view('livewire.manage-team-review-settings', [
            'roles' => (new ReviewSettings())->assignableUserRoles,
        ]);
    }
}
