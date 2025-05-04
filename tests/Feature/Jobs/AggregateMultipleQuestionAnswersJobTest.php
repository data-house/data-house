<?php

namespace Tests\Feature\Jobs;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\Exceptions\CopilotException;
use App\Copilot\Facades\Copilot;
use App\Jobs\AggregateMultipleQuestionAnswersJob;
use App\Jobs\AskMultipleQuestionJob;
use App\Jobs\AskQuestionJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Project;
use App\Models\Question;
use App\Models\QuestionRelation;
use App\Models\QuestionStatus;
use App\Models\QuestionTarget;
use App\Models\QuestionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AggregateMultipleQuestionAnswersJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_answer_to_multiple_question_aggregated(): void
    {
        $copilot = Copilot::fake();

        Queue::fake();

        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(2),
            )
            ->create();

        $documents = $collection->documents;

        
        $copilot->withAggregation(new CopilotResponse("Aggregated answer.", [
                [
                    "id" => $documents->first()->getCopilotKey(),
                    "page_number" => 2,
                ],
                [
                    "id" => $documents->last()->getCopilotKey(),
                    "page_number" => 4,
                ]
            ]));
        
        $question = Question::factory()
            ->multiple()
            ->recycle($collection)
            ->create([
                'question' => 'Do you really reply to my question?',
                'language' => 'en',
                'status' => QuestionStatus::ANSWERING,
                'execution_time' => null,
            ]);

        $subQuestions = Question::factory()
            ->count(2)
            ->answered()
            ->sequence(
                [
                    'questionable_id' => $documents->first()->getKey(),
                    'execution_time' => 120,
                ],
                [
                    'questionable_id' => $documents->last()->getKey(),
                    'execution_time' => 200,
                ],
            )
            ->create([
                'language' => 'en',
            ]);

        $subQuestions->each(function($q) use ($question): void {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });
        
        (new AggregateMultipleQuestionAnswersJob($question))->handle();

        $answers = $subQuestions->map(function($q){
            return ['id' => $q->uuid, 'text' => $q->answer['text'], 'lang' => $q->language, 'refs' => $q->answer['references']];
        });
        
        $answersAppend = $subQuestions->map(function($q){
            return ['id' => $q->uuid, 'text' => "## Document **{$q->questionable->title}**" . PHP_EOL . PHP_EOL];
        });

        $copilot->assertAggregationFor(new AnswerAggregationCopilotRequest($question->uuid, $question->question, $answers->all(), 'en', '0', $answersAppend->all()));

        $savedQuestion = $question->fresh();

        $this->assertEquals('Aggregated answer.', $savedQuestion->answer['text']);
        $this->assertEquals([
            [
                "id" => $documents->first()->getCopilotKey(),
                "page_number" => 2,
            ],
            [
                "id" => $documents->last()->getCopilotKey(),
                "page_number" => 4,
            ]
            ], $savedQuestion->answer['references']);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::PROCESSED, $savedQuestion->status);
        $this->assertNotNull($savedQuestion->user);
        $this->assertEquals('en', $savedQuestion->language);
        $this->assertNotNull($savedQuestion->execution_time);
        $this->assertGreaterThan(320, $savedQuestion->execution_time);

        $relatedQuestions = $savedQuestion->related;

        $this->assertEquals(2, $relatedQuestions->count());

        $relatedQuestionables = $relatedQuestions->map->questionable_id->sort()->toArray();

        $this->assertEquals([$documents->first()->getKey(), $documents->last()->getKey()], $relatedQuestionables);

        $related = $relatedQuestions->first();

        $this->assertEquals(QuestionStatus::PROCESSED, $related->status);
        $this->assertEquals(QuestionTarget::SINGLE, $related->target);
        $this->assertEquals(QuestionRelation::CHILDREN, $related->pivot->type);
        $this->assertNotNull($related->user);
        $this->assertEquals('en', $related->language);
        $this->assertEquals(120, $related->execution_time);

    }

    public function test_answer_to_multiple_document_with_project_question_aggregated(): void
    {
        $copilot = Copilot::fake();

        Queue::fake();

        $user = User::factory()->manager()->create();

        $project = Project::factory()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->for($project)->count(2),
            )
            ->create();

        $documents = $collection->documents;
        
        $question = Question::factory()
            ->multiple()
            ->recycle($collection)
            ->create([
                'question' => 'Do you really reply to my question?',
                'language' => 'en',
                'status' => QuestionStatus::ANSWERING,
                'execution_time' => null,
            ]);

        $subQuestions = Question::factory()
            ->count(2)
            ->answered()
            ->sequence(
                [
                    'questionable_id' => $documents->first()->getKey(),
                    'execution_time' => 120,
                ],
                [
                    'questionable_id' => $documents->last()->getKey(),
                    'execution_time' => 200,
                ],
            )
            ->create([
                'language' => 'en',
            ]);

        $subQuestions->each(function($q) use ($question): void {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });

        $copilot->withAggregation(new CopilotResponse("Aggregated answer.", [
            [
                "id" => $documents->first()->getCopilotKey(),
                "page_number" => 2,
            ],
            [
                "id" => $documents->last()->getCopilotKey(),
                "page_number" => 4,
            ]
        ]));

        (new AggregateMultipleQuestionAnswersJob($question))->handle();

        $answers = $subQuestions->map(function($q){
            return ['id' => $q->uuid, 'text' => $q->answer['text'], 'lang' => $q->language, 'refs' => $q->answer['references']];
        });
        
        $answersAppend = $subQuestions->map(function($q) use ($project){
            return ['id' => $q->uuid, 'text' => "## Project **{$project->title}**" . PHP_EOL . PHP_EOL];
        });

        $copilot->assertAggregationFor(new AnswerAggregationCopilotRequest($question->uuid, $question->question, $answers->all(), 'en', '0', $answersAppend->all()));

        $savedQuestion = $question->fresh();

        $this->assertEquals('Aggregated answer.', $savedQuestion->answer['text']);
        $this->assertEquals([
            [
                "id" => $documents->first()->getCopilotKey(),
                "page_number" => 2,
            ],
            [
                "id" => $documents->last()->getCopilotKey(),
                "page_number" => 4,
            ]
            ], $savedQuestion->answer['references']);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::PROCESSED, $savedQuestion->status);
        $this->assertNotNull($savedQuestion->user);
        $this->assertEquals('en', $savedQuestion->language);
        $this->assertNotNull($savedQuestion->execution_time);
        $this->assertGreaterThan(320, $savedQuestion->execution_time);

        $relatedQuestions = $savedQuestion->related;

        $this->assertEquals(2, $relatedQuestions->count());

        $relatedQuestionables = $relatedQuestions->map->questionable_id->sort()->toArray();

        $this->assertEquals([$documents->first()->getKey(), $documents->last()->getKey()], $relatedQuestionables);

        $related = $relatedQuestions->first();

        $this->assertEquals(QuestionStatus::PROCESSED, $related->status);
        $this->assertEquals(QuestionTarget::SINGLE, $related->target);
        $this->assertEquals(QuestionRelation::CHILDREN, $related->pivot->type);
        $this->assertNotNull($related->user);
        $this->assertEquals('en', $related->language);
        $this->assertEquals(120, $related->execution_time);

    }

    public function test_answer_to_multiple_question_aggregated_using_descriptive_template(): void
    {
        $copilot = Copilot::fake();

        Queue::fake();

        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(2),
            )
            ->create();

        $documents = $collection->documents;
        
        $question = Question::factory()
            ->multiple()
            ->recycle($collection)
            ->create([
                'question' => 'Do you really reply to my question?',
                'language' => 'en',
                'status' => QuestionStatus::ANSWERING,
                'execution_time' => null,
                'type' => QuestionType::DESCRIPTIVE,
            ]);

        $subQuestions = Question::factory()
            ->count(2)
            ->answered()
            ->sequence(
                [
                    'questionable_id' => $documents->first()->getKey(),
                    'execution_time' => 120,
                ],
                [
                    'questionable_id' => $documents->last()->getKey(),
                    'execution_time' => 200,
                ],
            )
            ->create([
                'language' => 'en',
            ]);

        $subQuestions->each(function($q) use ($question): void {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });

        $copilot->withAggregation(new CopilotResponse("Aggregated answer.", [
            [
                "id" => $documents->first()->getCopilotKey(),
                "page_number" => 2,
            ],
            [
                "id" => $documents->last()->getCopilotKey(),
                "page_number" => 4,
            ]
        ]));

        (new AggregateMultipleQuestionAnswersJob($question))->handle();

        $answers = $subQuestions->map(function($q){
            return ['id' => $q->uuid, 'text' => $q->answer['text'], 'lang' => $q->language, 'refs' => $q->answer['references']];
        });

        $answersAppend = $subQuestions->map(function($q) use ($question){
            return ['id' => $q->uuid, 'text' => "## Document **{$q->questionable->title}**" . PHP_EOL . PHP_EOL];
        });

        $copilot->assertAggregationFor(new AnswerAggregationCopilotRequest($question->uuid, $question->question, $answers->all(), 'en', '1', $answersAppend->all()));

        $savedQuestion = $question->fresh();

        $this->assertEquals('Aggregated answer.', $savedQuestion->answer['text']);
        $this->assertEquals([
            [
                "id" => $documents->first()->getCopilotKey(),
                "page_number" => 2,
            ],
            [
                "id" => $documents->last()->getCopilotKey(),
                "page_number" => 4,
            ]
            ], $savedQuestion->answer['references']);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::PROCESSED, $savedQuestion->status);
        $this->assertEquals(QuestionType::DESCRIPTIVE, $savedQuestion->type);
        $this->assertNotNull($savedQuestion->user);
        $this->assertEquals('en', $savedQuestion->language);
        $this->assertNotNull($savedQuestion->execution_time);
        $this->assertGreaterThan(320, $savedQuestion->execution_time);

        $relatedQuestions = $savedQuestion->related;

        $this->assertEquals(2, $relatedQuestions->count());

        $relatedQuestionables = $relatedQuestions->map->questionable_id->sort()->toArray();

        $this->assertEquals([$documents->first()->getKey(), $documents->last()->getKey()], $relatedQuestionables);

        $related = $relatedQuestions->first();

        $this->assertEquals(QuestionStatus::PROCESSED, $related->status);
        $this->assertEquals(QuestionTarget::SINGLE, $related->target);
        $this->assertEquals(QuestionRelation::CHILDREN, $related->pivot->type);
        $this->assertNotNull($related->user);
        $this->assertEquals('en', $related->language);
        $this->assertEquals(120, $related->execution_time);

    }


    public function test_job_re_enqued_when_pending_answers()
    {
        $copilot = Copilot::fake();

        Http::preventStrayRequests();

        Queue::fake();

        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(2),
            )
            ->create();

        $documents = $collection->documents;
        
        $question = Question::factory()
            ->multiple()
            ->recycle($collection)
            ->create([
                'question' => 'Do you really reply to my question?',
                'language' => 'en',
                'status' => QuestionStatus::ANSWERING,
                'execution_time' => null,
            ]);

        $subQuestions = Question::factory()
            ->count(2)
            ->sequence(
                [
                    'questionable_id' => $documents->first()->getKey(),
                    'status' => QuestionStatus::ANSWERING,
                ],
                [
                    'questionable_id' => $documents->last()->getKey(),
                    'status' => QuestionStatus::CREATED,
                ],
            )
            ->create([
                'language' => 'en',
            ]);

        $subQuestions->each(function($q) use ($question): void {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });
        
        (new AggregateMultipleQuestionAnswersJob($question))->handle();

        Queue::assertPushed(AggregateMultipleQuestionAnswersJob::class, function($job) use ($question) {
            return $job->question->is($question);
        });

        $savedQuestion = $question->fresh();

        $this->assertNotNull($savedQuestion);
        $this->assertNull($savedQuestion->answer);
        $this->assertEquals(QuestionStatus::ANSWERING, $savedQuestion->status);
    }

    public function test_empty_child_answers_handled(): void
    {
        $copilot = Copilot::fake();

        Queue::fake();

        $user = User::factory()->manager()->create();

        $collection = Collection::factory()
            ->for($user)
            ->hasAttached(
                Document::factory()->count(2),
            )
            ->create();

        $documents = $collection->documents;
        
        $question = Question::factory()
            ->multiple()
            ->recycle($collection)
            ->create([
                'question' => 'Do you really reply to my question?',
                'language' => 'en',
                'status' => QuestionStatus::ANSWERING,
                'execution_time' => null,
            ]);

        $subQuestions = Question::factory()
            ->count(2)
            ->errored()
            ->sequence(
                [
                    'questionable_id' => $documents->first()->getKey(),
                    'execution_time' => 120,
                ],
                [
                    'questionable_id' => $documents->last()->getKey(),
                    'execution_time' => 200,
                ],
            )
            ->create([
                'language' => 'en',
            ]);

        $subQuestions->each(function($q) use ($question): void {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });

        $copilot->withAggregation(new CopilotResponse("Aggregated answer.", [
            [
                "id" => $documents->first()->getCopilotKey(),
                "page_number" => 2,
            ],
            [
                "id" => $documents->last()->getCopilotKey(),
                "page_number" => 4,
            ]
        ]));

        $this->expectException(CopilotException::class);
        $this->expectExceptionMessage("No answers to aggregate");

        (new AggregateMultipleQuestionAnswersJob($question))->handle();

        $answers = $subQuestions->map(function($q){
            return ['id' => $q->uuid, 'text' => $q->answer['text'], 'lang' => $q->language, 'refs' => $q->answer['references']];
        });

        $answersAppend = $subQuestions->map(function($q) use ($question){
            return ['id' => $q->uuid, 'text' => "## Document **{$q->questionable->title}**" . PHP_EOL . PHP_EOL];
        });

        $copilot->assertAggregationFor(new AnswerAggregationCopilotRequest($question->uuid, $question->question, $answers->all(), 'en', '0', $answersAppend->all()));

    }
}
