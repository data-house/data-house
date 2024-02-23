<?php

namespace Tests\Feature;

use App\Actions\RecognizeLanguage as ActionsRecognizeLanguage;
use App\Jobs\Pipeline\Document\RecognizeLanguage;
use App\Models\Document;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use App\PdfProcessing\Facades\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class RecognizeLanguageJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_language_recognized(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $job = new RecognizeLanguage($model, $model->latestPipelineRun);

        $job->handle(app()->make(ActionsRecognizeLanguage::class));

        $document = $model->fresh();

        $this->assertNotNull($document->languages);
        $this->assertEquals(collect(LanguageAlpha2::English), $document->languages);
    }
    
    public function test_no_language_stored_when_not_recognized(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-empty-doc.pdf')), 'test.pdf');

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $job = new RecognizeLanguage($model, $model->latestPipelineRun);

        $job->handle(app()->make(ActionsRecognizeLanguage::class));

        $document = $model->fresh();

        $this->assertTrue($document->languages->isEmpty());
    }
    
    public function test_no_language_stored_when_document_has_no_text(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-empty-doc.pdf')), 'test.pdf');

        Pdf::shouldReceive('driver')->andReturn(new SmalotPdfParserDriver());
        Pdf::shouldReceive('text')->andReturn(new DocumentContent(''));

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $job = new RecognizeLanguage($model, $model->latestPipelineRun);

        $job->handle(app()->make(ActionsRecognizeLanguage::class));

        $document = $model->fresh();

        $this->assertTrue($document->languages->isEmpty());
    }
}
