<?php

namespace Database\Factories;

use App\Models\ImportSource;
use App\Models\ImportStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Import>
 */
class ImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'status' => fake()->randomElement(array_values(ImportStatus::cases())),
            'source' => ImportSource::WEBDAV->value,
            'configuration' => [
                'url' => 'http://service',
                'user' => 'fake-disk-user',
                'password' => 'fake-disk-password',
            ],
        ];
    }
}
