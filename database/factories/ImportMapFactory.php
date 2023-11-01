<?php

namespace Database\Factories;

use App\Models\Import;
use App\Models\ImportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImportMap>
 */
class ImportMapFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'import_id' => Import::factory(),
            'status' => ImportStatus::CREATED->value,
            'filters' => ['paths' => [fake()->filePath()]],
        ];
    }
}
