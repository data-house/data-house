<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use LivewireUI\Slideover\SlideoverComponent;

class TeamProjects extends SlideoverComponent
{

    public function mount()
    {
        $team = $this->team();

        abort_if(is_null($team), 403);

        $this->authorize('view', $team);
    }

    #[Computed()]
    public function team()
    {
        return auth()->user()->currentTeam;
    }

    #[Computed()]
    public function managed()
    {
        return $this->team()->managedProjects()->orderBy('title', 'ASC')->get();
    }
    
    #[Computed()]
    public function contributing()
    {
        return $this->team()->collaboratingProjects()->orderBy('title', 'ASC')->get();
    }

    
    public function render()
    {
        return view('livewire.team-projects');
    }
}
