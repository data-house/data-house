<?php

namespace Tests\Feature;

use App\Models\Disk;
use App\Models\Document;
use App\Models\Import;
use App\Models\ImportDocumentStatus;
use App\Models\ImportSource;
use App\Models\ImportStatus;
use App\Models\Visibility;
use App\Pipelines\Models\PipelineRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentDeleteCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_deleted(): void
    {
        config([
            'pdf.processors.extractor' => [
                'host' => 'http://localhost:9000',
            ],
            'copilot.engines.oaks' => [
                'host' => 'http://localhost:5000/',
            ],
        ]);

        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);
        
        $fakeDocumentDisk = Storage::fake(Disk::DOCUMENTS->value);

        $fakeWebdavDisk = Storage::fake('ondemand');

        $fakeWebdavDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');
        
        $fakeDocumentDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');


        $import = Import::factory()->create([
            'source' => ImportSource::LOCAL,
            'status' => ImportStatus::RUNNING,
            'configuration' => [
                'root' => $fakeWebdavDisk->path(''), // only used to make the import configuration looks correct
            ],
        ]);

        $importMap = $import->maps()->create([
            'status' => ImportStatus::RUNNING,
            'mapped_team' => null,
            'mapped_uploader' => $import->creator->getKey(),
            'recursive' => false,
            'filters' => [
                'paths' => "test.pdf"
            ],
        ]);

        $checksum = $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']);

        $importDocument = $importMap->documents()->create([
            'source_path' => "test.pdf",
            'disk_name' => "imports",
            'disk_path' => 'test.pdf',
            'mime' => "application/pdf",
            'uploaded_by' => $import->creator->getKey(),
            'team_id' => null,
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $checksum,
            'import_hash' => hash('sha256', 'test.pdf'),
        ]);

        $document = Document::withoutEvents(fn() => Document::create([
            'disk_name' => Disk::DOCUMENTS->value,
            'disk_path' => 'test.pdf',
            'title' => basename($importDocument->source_path),
            'mime' => $importDocument->mime,
            'uploaded_by' => $importDocument->uploaded_by,
            'team_id' => $importDocument->team_id,
            'document_hash' => $checksum,
            'document_date' => $importDocument->document_date,
            'document_size' => $importDocument->document_size,
            'visibility' => Visibility::defaultDocumentVisibility(),
            'properties' => [
                'filename' => basename($importDocument->source_path),
            ],
        ]));

        $importDocument->processed_at = now()->subHours(2);
        $importDocument->status = ImportDocumentStatus::COMPLETED;
        $importDocument->document_id = $document->getKey();
        $importDocument->save();

        $skippedImportDocument = $importMap->documents()->create([
            'source_path' => "test.pdf",
            'disk_name' => "imports",
            'disk_path' => 'test.pdf',
            'mime' => "application/pdf",
            'uploaded_by' => $import->creator->getKey(),
            'team_id' => null,
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $checksum,
            'import_hash' => hash('sha256', 'test.pdf'),
            'processed_at' => now(),
            'status' => ImportDocumentStatus::SKIPPED_DUPLICATE,
        ]);
        
        $skippedDifferentImportDocument = $importMap->documents()->create([
            'source_path' => "test.pdf",
            'disk_name' => "imports",
            'disk_path' => 'test.pdf',
            'mime' => "application/pdf",
            'uploaded_by' => $import->creator->getKey(),
            'team_id' => null,
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $checksum.'-',
            'import_hash' => hash('sha256', 'test.pdf'),
            'processed_at' => now(),
            'status' => ImportDocumentStatus::SKIPPED_DUPLICATE,
        ]);

        PipelineRun::factory()
            ->count(2)
            ->sequence(
                ['created_at' => now()->subHours(4)],
                ['created_at' => now()->subHours(2)],
            )
            ->for($document, 'pipeable')
            ->create();

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/documents/*' => Http::response([
                "id" => $document->getCopilotKey(),
                "status" => "ok"
            ], 200),
        ]);

        $this->artisan('document:delete', [
                'document' => $document->ulid,
            ])
            ->assertSuccessful();

        $this->assertEquals(0, $document->pipelineRuns()->count());

        $updatedDocument = $document->fresh();

        $this->assertNull($updatedDocument);

        $updatedImportDocument = $importDocument->fresh();
        
        $updatedSkippedImportDocument = $skippedImportDocument->fresh();
        
        $updatedSkippedDifferentImportDocument = $skippedDifferentImportDocument->fresh();

        $this->assertNull($updatedImportDocument);
        
        $this->assertNull($updatedSkippedImportDocument);
        
        $this->assertNotNull($updatedSkippedDifferentImportDocument);
        
        Storage::disk(Disk::DOCUMENTS->value)->assertMissing($document->disk_path);
        
        Storage::disk(Disk::IMPORTS->value)->assertMissing($importDocument->disk_path);
        
        $fakeWebdavDisk->assertMissing($importDocument->source_path);

        Queue::assertNothingPushed();
    }
}
