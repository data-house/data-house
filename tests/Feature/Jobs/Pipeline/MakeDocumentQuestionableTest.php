<?php

namespace Tests\Feature\Jobs\Pipeline;

use App\Jobs\Pipeline\Document\MakeDocumentQuestionable;
use App\Models\Document;
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
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
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
            'http://localhost:5000/library/library-id/*' => Http::response([
                "message" => "Document `{$model->getCopilotKey()}` removed from the library `library-id`."
            ], 200),
        ]);

        $job = new MakeDocumentQuestionable($model, $model->latestPipelineRun);

        $job->handle();

        Http::assertSentCount(2);
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
            'http://localhost:5000/library/library-id/*' => Http::response([
                "message" => "Document `{$model->getCopilotKey()}` removed from the library `library-id`."
            ], 200),
        ]);

        $job = new MakeDocumentQuestionable($model, $model->latestPipelineRun);

        $job->handle();

        Http::assertSentCount(0);
    }
    
    
}
