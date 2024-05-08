<?php

namespace Tests\Feature;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Project\AddProjectMember;
use App\Actions\Project\RemoveProjectMember;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RemoveProjectMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_removed(): void
    {
        $project = Project::factory()->withTeam()->create();

        $team = $project->teams()->first();

        app(RemoveProjectMember::class)->remove($team, $project);

        $fresh_project = $project->fresh();

        $this->assertFalse($fresh_project->belongsToTeam($team));
    }

}
