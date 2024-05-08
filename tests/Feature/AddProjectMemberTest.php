<?php

namespace Tests\Feature;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Project\AddProjectMember;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddProjectMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_added(): void
    {
        $team = Team::factory()->create(['personal_team' => false]);

        $project = Project::factory()->create();

        app(AddProjectMember::class)->add($project, $team, 'manager');

        $fresh_project = $project->fresh();

        $this->assertTrue($fresh_project->belongsToTeam($team));
    }

}
