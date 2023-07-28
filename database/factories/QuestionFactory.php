<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Document;
use App\Models\QuestionStatus;
use App\Models\QuestionTarget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => fake()->sentence(),
            'user_id' => User::factory(),
            'questionable_id' => Document::factory(),
            'hash' => function (array $attributes){
                return hash('sha512', $attributes['question'] . '-' . $attributes['questionable_id']);
            },
            'questionable_type' => Document::class,
            'language' => null,
            'answer' => null,
            'execution_time' => null,
            'status' => QuestionStatus::CREATED,
        ];
    }

    /**
     * Make the question answered
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function answered()
    {
        return $this->state(function (array $attributes) {
            return [
                'answer' => [
                    'text' => fake()->paragraph(),
                    'references' => [],
                ],
                'execution_time' => fake()->randomFloat(2, 10, 20),
                'status' => QuestionStatus::PROCESSED,
            ];
        });
    }
    
    public function multiple()
    {
        return $this->state(function (array $attributes) {
            return [
                'questionable_id' => Collection::factory(),
                'hash' => function (array $attributes){
                    $collection = Collection::find($attributes['questionable_id']);

                    $documents = $collection->documents()->select(['documents.'.((new Document())->getCopilotKeyName())])->get()->map->getCopilotKey();

                    return hash('sha512', $attributes['question'] . '-' . $documents->join('-'));
                },
                'questionable_type' => Collection::class,
                'target' => QuestionTarget::MULTIPLE,
            ];
        });
    }
}
