<?php

namespace Database\Factories;

use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CatalogValue>
 */
class CatalogValueFactory extends Factory
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
            'catalog_entry_id' => CatalogEntry::factory(),
            'catalog_field_id' => CatalogField::factory(),

            'value_text' => fake()->sentence(),
            'value_int' => null,
            'value_date' => null,
            'value_float' => null,
            'value_bool' => null,
            'value_concept' => null,
        ];
    }
}
