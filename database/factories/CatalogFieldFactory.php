<?php

namespace Database\Factories;

use App\Models\Catalog;
use App\CatalogFieldType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CatalogField>
 */
class CatalogFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'catalog_id' => Catalog::factory(),
            'user_id' => function($attributes){
                return Catalog::find($attributes['catalog_id'])->user->getKey();
            },
            'data_type' => CatalogFieldType::TEXT,
        ];
    }
}
