<?php

namespace Tests\Feature\Copilot;

use App\Copilot\CopilotResponse;
use App\Copilot\Engines\cloudEngine;
use App\Jobs\AskQuestionJob;
use App\Models\Disk;
use App\Models\Document;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionableTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_made_synchronously_questionable(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Http::preventStrayRequests();

        $textContent = [
            [
                "metadata" => [
                    "page_number" => 1
                ],
                "text" => "This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1"
            ],
        ];

        
        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
        ]);

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => $textContent,
                "status" => "ok"
            ], 200),
            'http://localhost:5000/library/library-id/documents' => Http::response([
                "message" => "Document {$document->getCopilotKey()} added to the library library-id."
            ], 201),
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        
        $document->questionable();

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents' &&
                   $request['id'] === $document->getCopilotKey() && 
                   $request['lang'] === 'en' &&
                   data_get($request['data'] ?? [], '0.title') == $document->title &&
                   data_get($request['data'] ?? [], '0.metadata.page_number') == 1 &&
                   data_get($request['data'] ?? [], '0.text') == $textContent[0]['text'];
        });

    }

    public function test_model_can_be_removed_synchronously_from_questionable(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        Http::preventStrayRequests();

        $textContent = [
            [
                "metadata" => [
                    "page_number" => 1
                ],
                "text" => "This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1"
            ],
        ];

        
        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
        ]);

        Http::fake([
            'http://localhost:5000/library/*' => Http::response([
                "message" => "Document `{$document->getCopilotKey()}` removed from the library `library-id`."
            ], 200),
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        
        $document->unquestionable();

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/library/library-id/documents/' . $document->getCopilotKey() &&
                $request->method() === 'DELETE';
        });

    }

    public function test_driver_instance_returned()
    {

        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        $document = Document::factory()->create();

        $driver = $document->questionableUsing();

        $this->assertInstanceOf(cloudEngine::class, $driver);
    }

    public function test_should_be_questionable()
    {

        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id',
            ],
        ]);

        $document = Document::factory()->create();

        $this->assertTrue($document->shouldBeQuestionable());
    }

    public function test_model_can_be_questioned(): void
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
        
        $document = Document::factory()->create();

        /**
         * @var \App\Models\Question
         */
        $answer = null;

        $questionUuid = null;

        $expectedQuestionHash = hash('sha512', 'Do you really reply to my question?-' . $document->getCopilotKey());

        Str::freezeUuids(function($uuid) use ($document, &$answer, &$questionUuid){

            $answer = $document->question('Do you really reply to my question?');

            $questionUuid = $uuid;
        });


        Queue::assertPushed(AskQuestionJob::class, function($job) use ($answer) {
            return $job->question->is($answer);
        });

        Http::assertNothingSent();

        $this->assertNotNull($questionUuid);

        $this->assertInstanceOf(Question::class, $answer);

        $savedQuestion = Question::whereUuid($questionUuid)->first();

        $this->assertNotNull($savedQuestion);

        $this->assertNull($savedQuestion->user);
        $this->assertNull($savedQuestion->language);
        $this->assertNull($savedQuestion->execution_time);
        $this->assertNull($savedQuestion->answer);

        $this->assertTrue($savedQuestion->questionable->is($document));

        $this->assertEquals($expectedQuestionHash, $savedQuestion->hash);
        $this->assertEquals('Do you really reply to my question?', $savedQuestion->question);

    }
}
