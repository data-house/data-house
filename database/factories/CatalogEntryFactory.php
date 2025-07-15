<?php

namespace Database\Factories;

use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CatalogEntry>
 */
class CatalogEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'catalog_id' => Catalog::factory(),
            'document_id' => null,
            'project_id' => null,
        ];
    }
}
