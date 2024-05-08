<?php

namespace Tests\Feature;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Project\AddProjectMember;
use App\Actions\Project\RemoveProjectMember;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class HasResponsibleTeamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_managed_by_team(): void
    {
        $project = Project::factory()->withTeam()->create();

        $team = $project->teams()->first();

        $user = User::factory()->admin()->create();

        $this->assertTrue($project->belongsToTeam($team));
        
        $this->assertNull($project->teamRole($team));

        $this->assertFalse($project->hasTeamRole($team, Role::CONTRIBUTOR->value));
        
        $this->assertEquals(['project:view', 'document:view'], $project->teamPermissions($team));
        
        $this->assertFalse($project->hasTeamPermission($team, 'document:create'));
    }

    public function test_project_managed_by_team_with_role(): void
    {
        $project = Project::factory()->withTeam(pivotAttributes: ['role' => Role::CONTRIBUTOR->value])->create();

        $team = $project->teams()->first();

        $user = User::factory()->admin()->create();

        $this->assertTrue($project->belongsToTeam($team));
        
        $this->assertEquals('contributor', $project->teamRole($team)->key);

        $this->assertTrue($project->hasTeamRole($team, Role::CONTRIBUTOR->value));
        
        $this->assertEquals(Jetstream::findRole(Role::CONTRIBUTOR->value)->permissions, $project->teamPermissions($team));
        
        $this->assertTrue($project->hasTeamPermission($team, 'project:update'));
    }


}
