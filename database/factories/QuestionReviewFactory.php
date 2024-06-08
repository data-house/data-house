<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Question;
use App\Models\QuestionStatus;
use App\Models\QuestionTarget;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuestionReview>
 */
class QuestionReviewFactory extends Factory
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
            'team_id' => Team::factory(),
        ];
    }

    
}
