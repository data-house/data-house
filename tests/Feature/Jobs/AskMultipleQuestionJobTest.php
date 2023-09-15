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
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AskMultipleQuestionJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_decomposed_and_children_queued(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
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
            ]);

        
        Http::fake([
            'http://localhost:5000/transform-question' => Http::response($documents->mapWithKeys(function($doc){
                return [$doc->getCopilotKey() => 'Question to execute on document'];})->toArray(), 200),
            'http://localhost:5000/question' => Http::response([
                "q_id" => $question->uuid,
                "answer" => [
                    [
                        "text" => "Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.",
                        "references" => [
                            [
                                "doc_id" => 1,
                                "page_number" => 2,
                            ],
                            [
                                "doc_id" => 1,
                                "page_number" => 4,
                            ]
                        ],
                    ],
                ]
            ], 200),
        ]);

        (new AskMultipleQuestionJob($question))->handle();

        Http::assertSent(function (Request $request) use ($question) {
            return $request->url() == 'http://localhost:5000/transform-question';
        });

        $savedQuestion = $question->fresh();

        $cachedResponse = Cache::get('q-' . $question->hash);

        $this->assertNotNull($cachedResponse);
        $this->assertInstanceOf(CopilotResponse::class, $cachedResponse);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::ANSWERING, $savedQuestion->status);
        $this->assertNotNull($savedQuestion->user);
        $this->assertEquals('en', $savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);

        $relatedQuestions = $savedQuestion->related;

        $this->assertEquals(2, $relatedQuestions->count());
        $this->assertEquals($documents->map->getKey()->toArray(), $relatedQuestions->map->questionable_id->toArray());

        $related = $relatedQuestions->first();

        $this->assertEquals(QuestionStatus::CREATED, $related->status);
        $this->assertEquals(QuestionTarget::SINGLE, $related->target);
        $this->assertEquals(QuestionRelation::CHILDREN, $related->pivot->type);
        $this->assertNull($related->user);
        $this->assertNull($related->team);
        $this->assertEquals('en', $related->language);
        $this->assertEquals(Visibility::TEAM, $related->visibility);
        $this->assertNull($related->execution_time);

        $relatedQuestions->each(function($q){
            Queue::assertPushed(AskQuestionJob::class, function($job) use ($q) {
                return $job->question->is($q);
            });
        });

        Queue::assertPushed(AggregateMultipleQuestionAnswersJob::class, function($job) use ($savedQuestion) {
            return $job->question->is($savedQuestion);
        });
    }

    public function test_descriptive_question_decomposed_and_children_queued(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
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
                'type' => QuestionType::DESCRIPTIVE,
            ]);

        
        Http::fake([
            'http://localhost:5000/transform-question' => Http::response($documents->mapWithKeys(function($doc){
                return [$doc->getCopilotKey() => ['Question to execute on document']];})->toArray(), 200),
            'http://localhost:5000/question' => Http::response([
                "q_id" => $question->uuid,
                "answer" => [
                    [
                        "text" => "Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.",
                        "references" => [
                            [
                                "doc_id" => 1,
                                "page_number" => 2,
                            ],
                            [
                                "doc_id" => 1,
                                "page_number" => 4,
                            ]
                        ],
                    ],
                ]
            ], 200),
        ]);

        (new AskMultipleQuestionJob($question))->handle();

        Http::assertSent(function (Request $request) use ($question) {
            return $request->url() == 'http://localhost:5000/transform-question' && 
                $request['template_id'] == '1';
        });

        $savedQuestion = $question->fresh();

        $cachedResponse = Cache::get('q-' . $question->hash);

        $this->assertNotNull($cachedResponse);
        $this->assertInstanceOf(CopilotResponse::class, $cachedResponse);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::ANSWERING, $savedQuestion->status);
        $this->assertEquals(QuestionType::DESCRIPTIVE, $savedQuestion->type);
        $this->assertNotNull($savedQuestion->user);
        $this->assertEquals('en', $savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);

        $relatedQuestions = $savedQuestion->related;

        $this->assertEquals(2, $relatedQuestions->count());
        $this->assertEquals($documents->map->getKey()->toArray(), $relatedQuestions->map->questionable_id->toArray());

        $related = $relatedQuestions->first();

        $this->assertEquals(QuestionStatus::CREATED, $related->status);
        $this->assertEquals(QuestionTarget::SINGLE, $related->target);
        $this->assertEquals(QuestionRelation::CHILDREN, $related->pivot->type);
        $this->assertNull($related->user);
        $this->assertEquals('en', $related->language);
        $this->assertNull($related->execution_time);

        $relatedQuestions->each(function($q){
            Queue::assertPushed(AskQuestionJob::class, function($job) use ($q) {
                return $job->question->is($q);
            });
        });

        Queue::assertPushed(AggregateMultipleQuestionAnswersJob::class, function($job) use ($savedQuestion) {
            return $job->question->is($savedQuestion);
        });
    }
    
}
