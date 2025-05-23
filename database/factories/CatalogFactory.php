<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Visibility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catalog>
 */
class CatalogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'user_id' => User::factory()->withPersonalTeam(),
            'team_id' => function($attributes){
                return User::find($attributes['user_id'])->currentTeam->getKey();
            },
            'visibility' => Visibility::PERSONAL,
        ];
    }

    public function visibleByAuthenticatedUsers(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => Visibility::PROTECTED,
            ];
        });
    }
}
