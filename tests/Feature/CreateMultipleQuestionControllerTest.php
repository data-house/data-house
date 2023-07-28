<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\Question;
use App\Models\QuestionTarget;
use App\Models\QuestionType;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateMultipleQuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_question_created_from_library(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->guest()->create();

        $collection = Collection::factory()->create([
            'visibility' => Visibility::SYSTEM,
            'strategy' => CollectionStrategy::LIBRARY,
            'title' => 'All Documents'
        ]);

        $response = $this->actingAs($user)
            ->from(route('documents.library'))
            ->post('/multiple-question', [
                'question' => 'This is a sample question',
                'strategy' => CollectionStrategy::LIBRARY->value,
            ]);

        $question = Question::first();

        $this->assertNotNull($question);

        $this->assertEquals(QuestionTarget::MULTIPLE, $question->target);
        $this->assertTrue($question->questionable->is($collection));

        $response->assertRedirectToRoute('questions.show', $question);
    }
}
