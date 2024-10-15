<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentSummary>
 */
class DocumentSummaryFactory extends Factory
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
            'ai_generated' => false,
            'text' => fake()->paragraph(),
            'language' => 'en',
        ];
    }


    public function forWholeDocument()
    {
        return $this->state(function (array $attributes) {
            return [
                'all_document' => true,
            ];
        });
    }

    public function forSection(?DocumentSection $section = null)
    {
        return $this->state(function (array $attributes) use ($section) {
            return [
                'all_document' => false,
                'document_section_id' => $section?->getKey() ?? DocumentSection::factory(),
            ];
        });
    }
}
