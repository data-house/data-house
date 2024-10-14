<?php

namespace Tests\Feature\Jobs\Pipeline;

use App\Jobs\Pipeline\Document\MakeDocumentQuestionable;
use App\Models\Document;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MakeDocumentQuestionableTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_document_questionable(): void
    {
        config([
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.features.summary' => false,
            'copilot.features.question' => true,
            'copilot.features.tagging' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->createQuietly([
                'properties' => [
                    'pages' => 20,
                    'has_textual_content' => true,
                ],
            ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/library/library-id/*' => Http::response([
                "message" => "Document `{$model->getCopilotKey()}` removed from the library `library-id`."
            ], 200),
        ]);

        $job = new MakeDocumentQuestionable($model, $model->latestPipelineRun);

        $job->handle();

        Http::assertSentCount(1);

        $pdfDriver->assertCount(1);
    }
    
    public function test_job_skipped_when_copilot_features_not_enabled(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'cloud',
            'copilot.queue' => false,
            'copilot.features.summary' => true,
            'copilot.features.question' => false,
            'copilot.features.tagging' => false,
            'copilot.engines.cloud' => [
                'host' => 'http://localhost:5000/',
                'library' => 'library-id'
            ],
        ]);

        Storage::fake('local');

        $pdfDriver = Pdf::fake('parse', [
            new DocumentContent("Content of the document")
        ]);

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->createQuietly([
                'properties' => [
                    'pages' => 20,
                ],
            ]);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/library/library-id/*' => Http::response([
                "message" => "Document `{$model->getCopilotKey()}` removed from the library `library-id`."
            ], 200),
        ]);

        $job = new MakeDocumentQuestionable($model, $model->latestPipelineRun);

        $job->handle();

        Http::assertSentCount(0);

        $pdfDriver->assertNoParsingRequests();
    }
    
    
}
