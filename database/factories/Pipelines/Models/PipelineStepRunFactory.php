<?php

namespace Database\Factories\Pipelines\Models;

use App\Pipelines\Models\PipelineStepRun;
use App\Pipelines\PipelineState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Pipelines\Models\PipelineStepRun>
 */
class PipelineStepRunFactory extends Factory
{
    protected $model = PipelineStepRun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(PipelineState::cases())->value,
            'job' => 'jobname',
        ];
    }
}
