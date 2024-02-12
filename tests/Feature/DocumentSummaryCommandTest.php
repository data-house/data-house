<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PrinsFrank\Standards\Language\LanguageAlpha2;
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

        $summary = $updatedDocument->summaries()->first();

        $this->assertInstanceOf(DocumentSummary::class, $summary);
        $this->assertEquals('Summary.', $summary->text);
        $this->assertEquals(LanguageAlpha2::English, $summary->language);
        $this->assertTrue($summary->ai_generated);
        $this->assertNull($updatedDocument->description);
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

        $document = Document::factory()
            ->has(DocumentSummary::factory()->state([
                'text' => 'Existing summary',
            ]), 'summaries')
            ->create([
                'properties' => [
                    'pages' => 20,
                ],
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

        $existingSummary = $updatedDocument->summaries()->where('ai_generated', false)->first();

        $this->assertInstanceOf(DocumentSummary::class, $existingSummary);
        $this->assertEquals('Existing summary', $existingSummary->text);
        $this->assertFalse($existingSummary->ai_generated);

        $generatedSummary = $updatedDocument->summaries()->where('ai_generated', true)->first();

        $this->assertInstanceOf(DocumentSummary::class, $generatedSummary);
        $this->assertEquals('Summary.', $generatedSummary->text);
        $this->assertTrue($generatedSummary->ai_generated);
    }
    
    public function test_multiple_ai_generate_abstracts_cohexists(): void
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

        $document = Document::factory()
            ->has(DocumentSummary::factory()->state([
                'text' => 'Existing summary',
                'ai_generated' => true,
            ]), 'summaries')
            ->create([
                'properties' => [
                    'pages' => 20,
                ],
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

        $this->assertEquals(2, $updatedDocument->summaries()->where('ai_generated', true)->count());
    }
}
