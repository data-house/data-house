<?php

namespace Tests\Feature;

use App\Actions\ClassifyDocumentType;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtractDocumentPropertiesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_properties_extracted_from_pdf_document(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $job = new ExtractDocumentProperties($model, $model->latestPipelineRun);

        $job->handle(app()->make(ClassifyDocumentType::class));

        $document = $model->fresh();

        $this->assertNotNull($document->properties);
        $this->assertArrayHasKey('title', $document->properties);
        $this->assertEquals('Test document', $document->properties['title']);
    }

    public function test_newly_extracted_properties_are_added_to_existing_ones(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
                'properties' => ['filename' => 'test.pdf'],
            ]);

        $job = new ExtractDocumentProperties($model, $model->latestPipelineRun);

        $job->handle(app()->make(ClassifyDocumentType::class));

        $document = $model->fresh();

        $this->assertNotNull($document->properties);
        $this->assertArrayHasKey('title', $document->properties);
        $this->assertArrayHasKey('filename', $document->properties);
        $this->assertEquals('Test document', $document->properties['title']);
        $this->assertEquals('test.pdf', $document->properties['filename']);
    }
}
