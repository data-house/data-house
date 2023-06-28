<?php

namespace Tests\Feature\Copilot\Engines;

use App\Copilot\CopilotManager;
use App\Models\Disk;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OaksEngineTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_document_can_be_added(): void
    {

        config([
            'pdf.processors.copilot' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
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

        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $engine->update(Document::all());

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/documents' &&
                   $request['id'] == $document->getCopilotKey() &&
                   $request['key_name'] == $document->getCopilotKeyName() &&
                   $request['content'] == $textContent;
        });

    }
    
    public function test_document_can_be_removed_from_copilot(): void
    {

        config([
            'pdf.processors.copilot' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
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

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => $textContent,
                "status" => "ok"
            ], 200),
            'http://localhost:5000/documents/*' => Http::response([
                "id" => $document->getCopilotKey(),
                "status" => "ok"
            ], 200),
        ]);

        

        /**
         * @var \App\Copilot\Engines\Engine
         */
        $engine = app(CopilotManager::class)->driver('oaks');

        $engine->delete(Document::all());

        Http::assertSent(function (Request $request) use ($document, $textContent) {
            return $request->url() == 'http://localhost:5000/documents/' . $document->getCopilotKey() &&
                   $request->method() === 'DELETE';
        });

    }
}
