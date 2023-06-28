<?php

namespace Tests\Feature\Copilot;

use App\Copilot\Engines\OaksEngine;
use App\Models\Disk;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
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
            'pdf.processors.copilot' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
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
            'http://localhost:5000/documents' => Http::response([
                "id" => $document->getCopilotKey(),
                "status" => "ok"
            ], 200),
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        
        $document->questionable();

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/documents' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['key_name'] == $document->getCopilotKeyName() &&
                   $request['content'] == $textContent;
        });

    }

    public function test_model_can_be_removed_synchronously_from_questionable(): void
    {
        config([
            'pdf.processors.copilot' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
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
            'http://localhost:5000/documents/*' => Http::response([
                "id" => $document->getCopilotKey(),
                "status" => "ok"
            ], 200),
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        
        $document->unquestionable();

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/documents/' . $document->getCopilotKey();
        });

    }

    public function test_driver_instance_returned()
    {

        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        $document = Document::factory()->create();

        $driver = $document->questionableUsing();

        $this->assertInstanceOf(OaksEngine::class, $driver);
    }

    public function test_should_be_questionable()
    {

        config([
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        $document = Document::factory()->create();

        $this->assertTrue($document->shouldBeQuestionable());
    }
}
