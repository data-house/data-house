<?php

namespace Database\Factories;

use App\Models\CollectionStrategy;
use App\Models\CollectionType;
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
            'visibility' => Visibility::SYSTEM,
            'strategy' => CollectionStrategy::LIBRARY,
            'team_id' => null,
        ];
    }
}
