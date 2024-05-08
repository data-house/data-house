<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LinkTeamToProjectCommandTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_team_linked(): void
    {
        $team = Team::factory()->create(['personal_team' => false]);

        $project = Project::factory()->create();
        
        $this->artisan('project:link-team', [
                'projects' => [$project->ulid],
                '--team' => $team->id,
            ])
            ->assertExitCode(0);

        $fresh_project = $project->fresh();

        $this->assertTrue($fresh_project->belongsToTeam($team));
    }
    
    public function test_team_linked_with_role(): void
    {
        $team = Team::factory()->create(['personal_team' => false]);

        $project = Project::factory()->create();
        
        $this->artisan('project:link-team', [
                'projects' => [$project->ulid],
                '--team' => $team->id,
                '--role' => 'admin',
            ])
            ->assertExitCode(0);

        $fresh_project = $project->fresh();

        $this->assertTrue($fresh_project->belongsToTeam($team));
        $this->assertTrue($fresh_project->responsibleTeams()->first()->is($team));
    }
    
    public function test_team_asked_if_missing(): void
    {
        $team = Team::factory()->create(['personal_team' => false]);

        $project = Project::factory()->create();
        
        $this->artisan('project:link-team', [
                'projects' => [$project->ulid],
            ])
            ->expectsQuestion('Specify the team?', $team->name)
            ->assertExitCode(0);

        $fresh_project = $project->fresh();

        $this->assertTrue($fresh_project->belongsToTeam($team));
    }
}
