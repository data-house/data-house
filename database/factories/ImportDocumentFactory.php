<?php

namespace Database\Factories;

use App\Models\Disk;
use App\Models\ImportMap;
use App\Models\MimeType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImportDocument>
 */
class ImportDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'import_map_id' => ImportMap::factory(),
            'source_path' => fake()->name(),
            'disk_name' => Disk::IMPORTS->value,
            'disk_path' => null,
            'mime' => MimeType::APPLICATION_PDF,
            'uploaded_by' => User::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
