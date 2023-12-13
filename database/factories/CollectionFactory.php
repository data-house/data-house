<?php

namespace Database\Factories;

use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
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
            'user_id' => User::factory(),
            'type' => CollectionType::STATIC,
            'visibility' => Visibility::PERSONAL,
            'strategy' => CollectionStrategy::STATIC,
            'team_id' => null,
        ];
    }

    public function library()
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => Visibility::PROTECTED,
                'strategy' => CollectionStrategy::LIBRARY,
            ];
        });
    }
    
    public function starred()
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => Visibility::SYSTEM,
                'strategy' => CollectionStrategy::STARRED,
            ];
        });
    }

    public function team()
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => Visibility::TEAM,
                'team_id' => Team::factory(),
            ];
        });
    }
}
