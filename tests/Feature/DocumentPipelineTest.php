<?php

namespace Tests\Feature;

use App\Jobs\Pipeline\Document\ConvertToPdf;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Jobs\Pipeline\Document\GenerateThumbnail;
use App\Jobs\Pipeline\Document\LinkDocumentWithAProject;
use App\Jobs\Pipeline\Document\MakeDocumentQuestionable;
use App\Jobs\Pipeline\Document\MakeDocumentSearchable;
use App\Jobs\Pipeline\Document\RecognizeLanguage;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_pipeline_dispatched(): void
    {
        Queue::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create();

        Queue::assertPushed(ExtractDocumentProperties::class, function($job) use ($document){
            return $job->model->is($document);
        });
        Queue::assertPushed(MakeDocumentSearchable::class, function($job) use ($document){
            return $job->model->is($document);
        });

        Queue::assertPushedWithChain(ExtractDocumentProperties::class, [
            LinkDocumentWithAProject::class,
            ConvertToPdf::class,
            RecognizeLanguage::class,
            GenerateThumbnail::class,
            MakeDocumentSearchable::class,
            MakeDocumentQuestionable::class,
        ]);

        Queue::assertPushed(MakeDocumentSearchable::class, 1);
    }
}
