<?php

namespace Tests\Feature\Jobs;

use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\Exceptions\CopilotException;
use App\Copilot\Facades\Copilot;
use App\Jobs\AskQuestionJob;
use App\Models\Question;
use App\Models\QuestionStatus;
use Exception;
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
        $question = Question::factory()->create([
            'question' => 'Do you really reply to my question?',
        ]);

        $documentKey = $question->questionable->getCopilotKey();

        $copilot = Copilot::fake()
            ->withAnswer(new CopilotResponse('Yes, I can provide information and answer questions related to renewable energy and sustainable development based on the context information provided.', [
                [
                    "id" => $documentKey,
                    "page_number" => 2,
                ],
                [
                    "id" => $documentKey,
                    "page_number" => 4,
                ]
                ]));

        Queue::fake();
        
        (new AskQuestionJob($question))->handle();

        $copilot->assertQuestionFor(new CopilotRequest($question->uuid, 'Do you really reply to my question?', $documentKey, 'en'));

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
        $copilot = Copilot::fake()
            ->withAnswer(new CopilotException('Field required'));

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

        $copilot->assertQuestionFor(new CopilotRequest($question->uuid, 'Do you really reply to my question?', $question->questionable->getCopilotKey(), 'en'));

        $savedQuestion = $question->fresh();

        $this->assertNull($savedQuestion->answer);
        
        $cachedResponse = Cache::get('q-' . $question->hash);

        $this->assertNull($cachedResponse);

        $this->assertNotNull($savedQuestion);

        $this->assertEquals(QuestionStatus::ERROR, $savedQuestion->status);
    }
}
