<?php

namespace Tests\Feature;

use App\Actions\Jetstream\AddTeamMember;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddTeamMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_member_added(): void
    {
        $team = Team::factory()->create(['personal_team' => false]);

        $user = User::factory()->manager()->create();

        app(AddTeamMember::class)->add($team->owner, $team, $user->email, 'manager');

        $fresh_user = $user->fresh();

        $this->assertEquals($team->getKey(), $fresh_user->current_team_id);

        $this->assertTrue($fresh_user->belongsToTeam($team));
    }

}
