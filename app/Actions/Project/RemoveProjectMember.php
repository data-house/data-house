<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class RemoveProjectMember
{
    /**
     * Remove the team from the given project.
     */
    public function remove(Team $team, Project $project): void
    {
        $project->teams()->detach($team);
    }
}
