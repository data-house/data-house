<?php

namespace Database\Factories;

use App\Models\FeedbackReason;
use App\Models\FeedbackVote;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuestionFeedback>
 */
class QuestionFeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'user_id' => User::factory(),
            'vote' => FeedbackVote::LIKE,
            'reason' => null,
            'points' => FeedbackVote::LIKE->points(),
            'note' => null,
        ];
    }

    /**
     * Create a negative feedback
     * 
     */
    public function negative(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'vote' => FeedbackVote::DISLIKE,
                'reason' => fake()->randomElement(FeedbackReason::cases()),
            ];
        });
    }
}
