<?php

namespace Tests\Feature\Jobs\Pipeline;

use App\Actions\SuggestDocumentAbstract;
use App\Jobs\Pipeline\Document\GenerateDocumentSummary;
use App\Models\Document;
use App\Models\DocumentSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class GenerateDocumentSummaryTest extends TestCase
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

        $model = Document::factory()
            ->hasPipelineRuns(1)
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
                "doc_id" => $model->ulid,
                "summary" => "Summary."
            ], 200),
        ]);

        $job = new GenerateDocumentSummary($model, $model->latestPipelineRun);

        $job->handle(app()->make(SuggestDocumentAbstract::class));

        $document = $model->fresh();

        $summary = $document->summaries()->first();

        $this->assertInstanceOf(DocumentSummary::class, $summary);
        $this->assertEquals('Summary.', $summary->text);
        $this->assertEquals(LanguageAlpha2::English, $summary->language);
        $this->assertTrue($summary->ai_generated);
        $this->assertNull($document->description);
    }
}
