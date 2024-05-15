<?php

namespace Tests\Feature\Jobs;

use App\Copilot\CopilotResponse;
use App\Copilot\Exceptions\CopilotException;
use App\Jobs\AggregateMultipleQuestionAnswersJob;
use App\Jobs\AskMultipleQuestionJob;
use App\Jobs\AskQuestionJob;
use App\Models\Collection;
use App\Models\Document;
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
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

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

        $subQuestions->each(function($q) use ($question) {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });
        
        Http::fake([
            'http://localhost:5000/library/library-id/questions/aggregate' => Http::response([
                "id" => $question->uuid,
                "lang" => 'en',
                "text" => "Aggregated answer.",
                "refs" => [
                    [
                        "id" => $documents->first()->getCopilotKey(),
                        "page_number" => 2,
                    ],
                    [
                        "id" => $documents->last()->getCopilotKey(),
                        "page_number" => 4,
                    ]
                ],
            ], 200),
        ]);

        (new AggregateMultipleQuestionAnswersJob($question))->handle();

        Http::assertSent(function (Request $request) use ($question, $subQuestions) {
            return $request->url() == 'http://localhost:5000/library/library-id/questions/aggregate' &&
                   $request->method() === 'POST' &&
                   filled($request['question'] ?? []) &&
                   filled($request['transformation'] ?? []) &&
                   filled($request['answers'] ?? []) &&
                   $request['transformation']['args'][0] == $question->question &&
                   $request['transformation']['id'] == '0' &&
                   $request['question']['id'] == $question->uuid &&
                   $request['question']['lang'] == 'en' &&
                   $request['question']['text'] == $question->question && 
                   $request['answers'][0]['text'] == $subQuestions->first()->answer['text'] &&
                   $request['answers'][0]['id'] == $subQuestions->first()->uuid
                   ;
        });

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
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

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

        $subQuestions->each(function($q) use ($question) {
            $question->related()->attach($q->getKey(), ['type' => QuestionRelation::CHILDREN]);
        });
        
        Http::fake([
            'http://localhost:5000/library/library-id/questions/aggregate' => Http::response([
                "id" => $question->uuid,
                "lang" => 'en',
                "text" => "Aggregated answer.",
                "refs" => [
                    [
                        "id" => $documents->first()->getCopilotKey(),
                        "page_number" => 2,
                    ],
                    [
                        "id" => $documents->last()->getCopilotKey(),
                        "page_number" => 4,
                    ]
                ],
            ], 200),
        ]);

        (new AggregateMultipleQuestionAnswersJob($question))->handle();

        Http::assertSent(function (Request $request) use ($question, $subQuestions) {
            return $request->url() == 'http://localhost:5000/library/library-id/questions/aggregate' &&
                $request->method() === 'POST' &&
                filled($request['question'] ?? []) &&
                filled($request['transformation'] ?? []) &&
                filled($request['answers'] ?? []) &&
                $request['transformation']['args'][0] == $question->question &&
                $request['transformation']['id'] == '1' &&
                $request['question']['id'] == $question->uuid &&
                $request['question']['lang'] == 'en' &&
                $request['question']['text'] == $question->question && 
                $request['answers'][0]['text'] == $subQuestions->first()->answer['text'];
    
        });

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
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

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

        $subQuestions->each(function($q) use ($question) {
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
    
}
