<?php

namespace Tests\Feature\Jobs\Pipeline;

use App\Copilot\Facades\Copilot;
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
            'copilot.features.summary' => false,
            'copilot.features.question' => true,
            'copilot.features.tagging' => false,
        ]);

        $copilot = Copilot::fake();

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

        $job = new MakeDocumentQuestionable($model, $model->latestPipelineRun);

        $job->handle();

        $pdfDriver->assertCount(1);

        $copilot->assertDocumentsPushed(1);
    }
    
    public function test_job_skipped_when_copilot_features_not_enabled(): void
    {
        config([
            'copilot.features.summary' => true,
            'copilot.features.question' => false,
            'copilot.features.tagging' => false,
        ]);

        $copilot = Copilot::fake();

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

        $job = new MakeDocumentQuestionable($model, $model->latestPipelineRun);

        $job->handle();

        $copilot->assertNoCopilotInteractions();

        $pdfDriver->assertNoParsingRequests();
    }
    
    
}
