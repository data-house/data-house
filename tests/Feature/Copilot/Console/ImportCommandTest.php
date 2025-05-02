<?php

namespace Tests\Feature\Copilot\Console;

use App\Copilot\Facades\Copilot;
use App\Models\Disk;
use App\Models\Document;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionable_models_can_be_imported(): void
    {
        config([
            'pdf.processors.copilot' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.driver' => 'null',
        ]);

        $copilot = Copilot::fake();

        Queue::fake();

        Storage::fake(Disk::DOCUMENTS->value);

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $pdfDriver = Pdf::fake(extractions: [
            new DocumentContent("This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1")
        ]);

        $document = Document::factory()->create([
            'disk_path' => 'test.pdf',
        ]);
        
        $additionalDocument = Document::factory()->create([
            'mime' => 'text/markdown',
            'disk_path' => 'test.md',
        ]);

        $this->artisan('copilot:import', [
                'model' => Document::class
            ])
            ->assertSuccessful()
            ->expectsOutputToContain('All [App\Models\Document] records have been imported.');

        $copilot->assertDocumentsPushed(1);
    }
}
