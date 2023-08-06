<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentSummaryCommandTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_abstract_generated(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        $document = Document::factory()->create([
            'properties' => [
                'pages' => 20,
            ],
            'description' => null,
        ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => [
                    [
                        "metadata" => [
                            "page_number" => 1
                        ],
                        "text" => "Content of the document"
                    ],
                ],
                "status" => "ok"
            ], 200),
            'http://localhost:5000/summarize' => Http::response([
                "doc_id" => $document->ulid,
                "summary" => "Summary."
            ], 200),
        ]);

        $this->artisan('document:summary', [
            'documents' => [$document->ulid],
        ])
        ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $this->assertEquals('Summary.', $updatedDocument->description);
    }
    
    public function test_abstract_not_overwritten_if_present(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'oaks',
            'copilot.queue' => false,
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        Storage::fake('local');

        $document = Document::factory()->create([
            'properties' => [
                'pages' => 20,
            ],
            'description' => 'Existing abstract',
        ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/extract-text' => Http::response([
                "content" => [
                    [
                        "metadata" => [
                            "page_number" => 1
                        ],
                        "text" => "Content of the document"
                    ],
                ],
                "status" => "ok"
            ], 200),
            'http://localhost:5000/summarize' => Http::response([
                "doc_id" => $document->ulid,
                "summary" => "Summary."
            ], 200),
        ]);

        $this->artisan('document:summary', [
            'documents' => [$document->ulid],
        ])
        ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $this->assertEquals('Existing abstract', $updatedDocument->description);
    }
}
