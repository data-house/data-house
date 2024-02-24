<?php

namespace App\Livewire;

use App\Data\UploadSettings;
use Livewire\Component;

class ManageTeamUploadSettings extends Component
{
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The "upload settings" form state.
     *
     * @var array
     */
    public $uploadSettingsForm = [
        'uploadLinkUrl' => '',
        'supportProjects' => false,
        'limitProjectsTo' => '',
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
        if($team->settings?->upload){
            $this->uploadSettingsForm = $team->settings->upload->toArray();
        }
    }

    /**
     * Add a new team member to a team.
     *
     * @return void
     */
    public function updateUploadSettings()
    {
        $this->resetErrorBag();

        $this->validate([
            'uploadSettingsForm.uploadLinkUrl' => 'required|url|max:250',
            'uploadSettingsForm.supportProjects' => 'bool',
            'uploadSettingsForm.limitProjectsTo' => 'string|max:200',
        ]);

        $this->team->settings->upload = UploadSettings::from($this->uploadSettingsForm);

        $this->team->save();

        $this->dispatch('saved');
    }


    public function render()
    {
        return view('livewire.manage-team-upload-settings');
    }
}
