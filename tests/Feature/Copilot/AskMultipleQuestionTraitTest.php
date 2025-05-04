<?php

namespace Tests\Feature\Copilot;

use App\Copilot\Facades\Copilot;
use App\Jobs\AskMultipleQuestionJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Question;
use App\Models\QuestionRelation;
use App\Models\QuestionTarget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AskMultipleQuestionTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_questioned(): void
    {
        $copilot = Copilot::fake();

        Http::preventStrayRequests();

        Queue::fake();
        
        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(3),
            )
            ->create();

        $documents = $collection->documents;

        /**
         * @var \App\Models\Question
         */
        $question = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $documents->map->getCopilotKey()->join('-'));

        Str::freezeUuids(function($uuid) use ($collection, &$question, &$questionUuid): void{

            $question = $collection->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });


        Queue::assertPushed(AskMultipleQuestionJob::class, function($job) use ($question) {
            return $job->question->is($question);
        });

        $copilot->assertNoInteractions();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $question);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertNull($savedQuestion->user);
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);
        $this->assertEquals(QuestionTarget::MULTIPLE, $savedQuestion->target);

        $this->assertTrue($savedQuestion->questionable->is($collection));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

    }

    public function test_same_question_can_be_asked_and_is_considered_a_retry(): void
    {
        $copilot = Copilot::fake();

        Queue::fake();
        
        $user = User::factory()->manager()->withPersonalTeam()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(3),
            )
            ->create();

        $existingQuestion = Question::factory()
            ->multiple($collection)
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->create([
                'question' => 'Do you really reply to my question?'
            ]);

        $documents = $collection->documents;

        /**
         * @var \App\Models\Question
         */
        $question = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $documents->map->getCopilotKey()->join('-'));

        $this->actingAs($user);

        Str::freezeUuids(function($uuid) use ($collection, &$question, &$questionUuid): void{

            $question = $collection->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });

        Queue::assertPushed(AskMultipleQuestionJob::class, function($job) use ($question) {
            return $job->question->is($question);
        });

        $copilot->assertNoInteractions();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $question);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertTrue($savedQuestion->user->is($user));
        $this->assertTrue($savedQuestion->team->is($user->currentTeam));
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);
        $this->assertEquals(QuestionTarget::MULTIPLE, $savedQuestion->target);

        $this->assertTrue($savedQuestion->questionable->is($collection));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

        $this->assertTrue($savedQuestion->related()->wherePivot('type', QuestionRelation::RETRY)->first()->is($existingQuestion));
    }

    public function test_same_question_can_be_asked_and_is_not_considered_a_retry(): void
    {
        $copilot = Copilot::fake();

        Queue::fake();
        
        $user = User::factory()->manager()->withPersonalTeam()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(3),
            )
            ->create();

        Question::factory()
            ->multiple($collection)
            ->create([
                'question' => 'Do you really reply to my question?'
            ]);

        $documents = $collection->documents;

        /**
         * @var \App\Models\Question
         */
        $question = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $documents->map->getCopilotKey()->join('-'));

        $this->actingAs($user);

        Str::freezeUuids(function($uuid) use ($collection, &$question, &$questionUuid): void{
            $question = $collection->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });

        Queue::assertPushed(AskMultipleQuestionJob::class, function($job) use ($question) {
            return $job->question->is($question);
        });

        $copilot->assertNoInteractions();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $question);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertTrue($savedQuestion->user->is($user));
        $this->assertTrue($savedQuestion->team->is($user->currentTeam));
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);
        $this->assertEquals(QuestionTarget::MULTIPLE, $savedQuestion->target);

        $this->assertTrue($savedQuestion->questionable->is($collection));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

        $this->assertNull($savedQuestion->related()->wherePivot('type', QuestionRelation::RETRY)->first());
    }
}
