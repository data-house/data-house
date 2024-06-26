<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\Flag;
use App\Models\Question;
use App\Models\QuestionTarget;
use App\Models\QuestionType;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class CreateMultipleQuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_question_created_from_library(): void
    {
        Feature::define(Flag::questionWholeLibraryWithAI(), true);
        
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->manager()->create();

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
        $this->assertEquals(QuestionType::FREE, $question->type);
        $this->assertTrue($question->questionable->is($collection));

        $response->assertRedirectToRoute('questions.show', $question);
    }
    
    public function test_multiple_question_not_created_from_library_if_feature_flag_disabled(): void
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

        $response->assertBadRequest();

        $this->assertNull(Question::first());
    }
    
    public function test_multiple_question_not_created_from_library_if_rate_limit_exceeded(): void
    {
        Feature::define(Flag::questionWholeLibraryWithAI(), true);

        config([
            'copilot.driver' => 'null',
            'copilot.queue' => false,
            'copilot.limits.questions_per_user_per_day' => 0,
        ]);

        Queue::fake();

        $user = User::factory()->withPersonalTeam()->manager()->create();

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

        $response->assertSessionHasErrors(['question' => 'You reached your daily limit of 0 questions/day. You will be able to ask questions tomorrow.']);

        $this->assertNull(Question::first());
    }

    public function test_multiple_question_created_from_collection(): void
    {
        Queue::fake();

        $team = Team::factory()
            ->state(['name' => 'Team','personal_team' => true])
            ->create();

        $user = User::factory()->recycle($team)
            ->withPersonalTeam()
            ->withCurrentTeam()
            ->guest()
            ->create();

        $collection = Collection::factory()->create([
            'visibility' => Visibility::PERSONAL,
            'strategy' => CollectionStrategy::STATIC,
            'title' => 'My Collection'
        ]);

        $response = $this->actingAs($user)
            ->from(route('collections.show', $collection))
            ->post('/multiple-question', [
                'question' => 'This is a sample question',
                'strategy' => CollectionStrategy::STATIC->value,
                'collection' => $collection->getKey(),
            ]);

        $question = Question::first();

        $this->assertNotNull($question);

        $this->assertEquals(QuestionTarget::MULTIPLE, $question->target);
        $this->assertEquals(QuestionType::FREE, $question->type);
        $this->assertTrue($question->questionable->is($collection));
        $this->assertTrue($question->user->is($user));
        $this->assertTrue($question->team->is($user->currentTeam));

        $response->assertRedirectToRoute('questions.show', $question);
    }

    public function test_collection_required_if_strategy_is_static(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->guest()->create();

        $collection = Collection::factory()->create([
            'visibility' => Visibility::PERSONAL,
            'strategy' => CollectionStrategy::STATIC,
            'title' => 'My Collection'
        ]);

        $response = $this->actingAs($user)
            ->from(route('collections.show', $collection))
            ->post('/multiple-question', [
                'question' => 'This is a sample question',
                'strategy' => CollectionStrategy::STATIC->value,
            ]);

        $response->assertRedirectToRoute('collections.show', $collection);

        $response->assertSessionHasErrors('collection');

        $question = Question::first();

        $this->assertNull($question);

    }

    public function test_guided_multiple_question_created(): void
    {
        Queue::fake();

        $user = User::factory()->withPersonalTeam()->guest()->create();

        $collection = Collection::factory()->create([
            'visibility' => Visibility::PERSONAL,
            'strategy' => CollectionStrategy::STATIC,
            'title' => 'My Collection'
        ]);

        $response = $this->actingAs($user)
            ->from(route('collections.show', $collection))
            ->post('/multiple-question', [
                'question' => 'This is a sample question',
                'strategy' => CollectionStrategy::STATIC->value,
                'collection' => $collection->getKey(),
                'guidance' => true,
            ]);

        $question = Question::first();

        $this->assertNotNull($question);

        $this->assertEquals(QuestionTarget::MULTIPLE, $question->target);
        $this->assertEquals(QuestionType::DESCRIPTIVE, $question->type);
        $this->assertTrue($question->questionable->is($collection));

        $response->assertRedirectToRoute('questions.show', $question);
    }
}
