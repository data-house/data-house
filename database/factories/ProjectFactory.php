<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use PrinsFrank\Standards\Country\CountryAlpha3;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->text(40),
            'topics' => [],
            'type' => fake()->randomElement(ProjectType::cases()),
            'countries' => [CountryAlpha3::Germany],
            'organizations' => [],
            'properties' => [],
            'slug' => fake()->slug(),
            'description' => fake()->paragraph(),
            'starts_at' => null,
            'ends_at' => null,
            'website' => fake()->url(),
        ];
    }

    /**
     * Indicate that the project should belong to team.
     *
     * @return $this
     */
    public function withTeam(array $teamState = [], array $pivotAttributes = []): static
    {
        return $this->hasAttached(
            Team::factory()
                ->state(fn (array $attributes, Project $project) => [
                    'name' => $project->name.'\'s Team',
                    'user_id' => User::factory(),
                    'personal_team' => false,
                    ...$teamState,
                ]),
            $pivotAttributes,
            'teams'
        );
    }
}
