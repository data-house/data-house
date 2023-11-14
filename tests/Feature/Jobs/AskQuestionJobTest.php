<?php

namespace Tests\Feature\Jobs;

use App\Copilot\CopilotResponse;
use App\Copilot\Exceptions\CopilotException;
use App\Jobs\AskQuestionJob;
use App\Models\Question;
use App\Models\QuestionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AskQuestionJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_executed(): void
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
        
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
        ]);

        
        Http::fake([
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

        (new AskQuestionJob($question))->handle();

        Http::assertSent(function (Request $request) use ($question) {
            return $request->url() == 'http://localhost:5000/question' &&
                   $request['q_id'] === $question->uuid &&
                   $request['q'] == 'Do you really reply to my question?' &&
                   $request['doc_id'][0] === ''.$question->questionable->getCopilotKey() &&
                   $request['lang'];
        });

        $savedQuestion = $question->fresh();

        $this->assertEquals('Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.', $savedQuestion->answer['text']);
        $this->assertEquals([
            [
                "doc_id" => 1,
                "page_number" => 2,
            ],
            [
                "doc_id" => 1,
                "page_number" => 4,
            ]
            ], $savedQuestion->answer['references']);

        $cachedResponse = Cache::get('q-' . $question->hash);

        $this->assertNotNull($cachedResponse);
        $this->assertInstanceOf(CopilotResponse::class, $cachedResponse);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::PROCESSED, $savedQuestion->status);
        $this->assertNotNull($savedQuestion->user);
        $this->assertEquals('en', $savedQuestion->language);
        $this->assertNotNull($savedQuestion->execution_time);
    }
    
    public function test_errors_are_handled(): void
    {
        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Http::preventStrayRequests();
        
        Http::fake([
            'http://localhost:5000/question' => Http::response([
                "code" => 422,
                "message" => "No content found in request",
                "type" => "Unprocessable Entity",
            ], 422),
        ]);
        
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
        ]);

        $job = new AskQuestionJob($question);

        try {
            $job->handle();
        } catch (CopilotException $th) {
            // This simulates what Laravel will do behind the scenes 
            $job->failed();
        }

        Http::assertSent(function (Request $request) use ($question) {
            return $request->url() == 'http://localhost:5000/question' &&
                   $request['q_id'] === $question->uuid &&
                   $request['q'] == 'Do you really reply to my question?' &&
                   $request['doc_id'][0] === ''.$question->questionable->getCopilotKey() &&
                   $request['lang'];
        });

        $savedQuestion = $question->fresh();

        $this->assertNull($savedQuestion->answer);
        
        $cachedResponse = Cache::get('q-' . $question->hash);

        $this->assertNull($cachedResponse);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::ERROR, $savedQuestion->status);
    }
}
