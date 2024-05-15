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
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Http::preventStrayRequests();

        Queue::fake();
        
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
        ]);

        $documentKey = $question->questionable->getCopilotKey();
        
        Http::fake([
            "http://localhost:5000/library/library-id/documents/{$documentKey}/questions" => Http::response([
                "id" => $question->uuid,
                "lang" => "en",
                "text" => "Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.",
                "refs" => [
                    [
                        "id" => $documentKey,
                        "page_number" => 2,
                    ],
                    [
                        "id" => $documentKey,
                        "page_number" => 4,
                    ]
                ],
            ], 200),
        ]);

        (new AskQuestionJob($question))->handle();

        Http::assertSent(function (Request $request) use ($question, $documentKey) {
            return $request->url() == "http://localhost:5000/library/library-id/documents/{$documentKey}/questions" &&
                   $request['id'] === $question->uuid &&
                   $request['text'] == 'Do you really reply to my question?' &&
                   $request['lang'];
        });

        $savedQuestion = $question->fresh();

        $this->assertEquals('Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.', $savedQuestion->answer['text']);
        $this->assertEquals([
            [
                "id" => $documentKey,
                "page_number" => 2,
            ],
            [
                "id" => $documentKey,
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
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Http::preventStrayRequests();

        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
        ]);
        
        Http::fake([
            "http://localhost:5000/library/library-id/documents/{$question->questionable->getCopilotKey()}/questions" => Http::response([
                "detail" => [
                    [
                      "type" => "missing",
                      "loc" => [
                        "body",
                        "text"
                      ],
                      "msg" => "Field required",
                      "input" => [
                        "id" => $question->uuid,
                        "lang" => $question->language
                      ]
                    ]
                  ]
            ], 422),
        ]);

        $job = new AskQuestionJob($question);

        try {
            $job->handle();
        } catch (CopilotException $th) {
            // This simulates what Laravel will do behind the scenes 
            $job->failed();
        }

        Http::assertSent(function (Request $request) use ($question) {
            return $request->url() == "http://localhost:5000/library/library-id/documents/{$question->questionable->getCopilotKey()}/questions" &&
                   $request['id'] === $question->uuid &&
                   $request['text'] == 'Do you really reply to my question?' &&
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
