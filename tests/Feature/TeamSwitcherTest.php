<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeamSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_switcher_visible_if_user_has_a_team(): void
    {
        $team = Team::factory()->create(['personal_team' => false]);

        $team->users()->attach(
            $user = User::factory()->manager()->create(),
            ['role' => 'manager']
        );

        $user->current_team_id = $team->id;
        $user->save();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertSuccessful();

        $response->assertSee('Manage Team');
        $response->assertSee($user->allTeams()->first()->name);
    }

    public function test_switcher_hidden_if_user_not_part_of_team(): void
    {
        $user = User::factory()->manager()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertSuccessful();
        $response->assertDontSee('Manage Team');
    }

    public function test_switcher_hidden_if_user_cannot_create_team(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertSuccessful();
        $response->assertDontSee('Manage Team');
    }
}
