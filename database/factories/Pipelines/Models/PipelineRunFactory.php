<?php

namespace Database\Factories\Pipelines\Models;

use App\Pipelines\Models\PipelineRun;
use App\Pipelines\PipelineState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Pipelines\Models\PipelineRun>
 */
class PipelineRunFactory extends Factory
{
    protected $model = PipelineRun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(PipelineState::cases())->value,
        ];
    }
}
