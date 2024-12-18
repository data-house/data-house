<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentSection>
 */
class DocumentSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'title' => fake()->sentence(),
            'order' => 1,
            'level' => 1,
            'reference' => null,
        ];
    }

}
