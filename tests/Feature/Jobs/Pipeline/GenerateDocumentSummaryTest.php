<?php

namespace Tests\Feature\Jobs\Pipeline;

use App\Actions\SuggestDocumentAbstract;
use App\Actions\Summary\SaveSummary;
use App\Copilot\CopilotResponse;
use App\Copilot\Facades\Copilot;
use App\Jobs\Pipeline\Document\GenerateDocumentSummary;
use App\Models\Document;
use App\Models\DocumentSummary;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
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
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

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

        $job = new GenerateDocumentSummary($model, $model->latestPipelineRun);

        $job->handle(app()->make(SuggestDocumentAbstract::class), app()->make(SaveSummary::class));

        $document = $model->fresh();

        $summary = $document->summaries()->first();

        $this->assertInstanceOf(DocumentSummary::class, $summary);
        $this->assertEquals('Summary.', $summary->text);
        $this->assertEquals(LanguageAlpha2::English, $summary->language);
        $this->assertTrue($summary->ai_generated);
        $this->assertNull($document->description);

        $pdfDriver->assertCount(1);

        $copilot->assertSummariesGenerated(1);
    }
    
    public function test_two_abstracts_generated_for_non_english_documents(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."))
            ->withSummary(new CopilotResponse("Summary."));

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
                'languages' => collect(LanguageAlpha2::Spanish_Castilian),
            ]);

        $job = new GenerateDocumentSummary($model, $model->latestPipelineRun);

        $job->handle(app()->make(SuggestDocumentAbstract::class), app()->make(SaveSummary::class));

        $document = $model->fresh();

        $summary = $document->summaries()->first();

        $this->assertEquals(2, $document->summaries()->count());

        $this->assertInstanceOf(DocumentSummary::class, $summary);
        $this->assertEquals('Summary.', $summary->text);
        $this->assertEquals(LanguageAlpha2::Spanish_Castilian, $summary->language);
        $this->assertTrue($summary->ai_generated);
        $this->assertTrue($summary->all_document);
        $this->assertNull($document->description);
        $pdfDriver->assertCount(2);

        $copilot->assertSummariesGenerated(2);
    }
    
    public function test_only_english_abstract_generated_for_unsupported_languages(): void
    {
        $copilot = Copilot::fake()
            ->withSummary(new CopilotResponse("Summary."));

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
                'languages' => collect(LanguageAlpha2::Icelandic),
            ]);

        $job = new GenerateDocumentSummary($model, $model->latestPipelineRun);

        $job->handle(app()->make(SuggestDocumentAbstract::class), app()->make(SaveSummary::class));

        $document = $model->fresh();

        $summary = $document->summaries()->first();

        $this->assertEquals(1, $document->summaries()->count());

        $this->assertInstanceOf(DocumentSummary::class, $summary);
        $this->assertEquals('Summary.', $summary->text);
        $this->assertEquals(LanguageAlpha2::English, $summary->language);
        $this->assertTrue($summary->ai_generated);
        $this->assertNull($document->description);
        $pdfDriver->assertCount(1);
        $copilot->assertSummariesGenerated(1);
    }
}
