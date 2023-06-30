<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ImportFileDataJob;
use App\Jobs\MoveImportedDocumentsJob;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Jobs\RetrieveDocumentsToImportJob;
use App\Models\Disk;
use App\Models\Document;
use App\Models\Import;
use App\Models\ImportDocument;
use App\Models\ImportMap;
use App\Models\ImportSource;
use App\Models\ImportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MoveImportedDocumentsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_imported_document_can_be_moved_to_document(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);
        
        $fakeDocumentDisk = Storage::fake(Disk::DOCUMENTS->value);

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');


        $import = Import::factory()->create([
            'source' => ImportSource::LOCAL,
            'status' => ImportStatus::RUNNING,
            'configuration' => [
                'root' => $fakeImportDisk->path(''), // only used to make the import configuration looks correct
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

        $importDocument = $importMap->documents()->create([
            'source_path' => "test.pdf",
            'disk_name' => "imports",
            'disk_path' => 'test.pdf',
            'mime' => "application/pdf",
            'uploaded_by' => $import->creator->getKey(),
            'team_id' => null
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $document = Document::first();

        $this->assertEquals('documents', $document->disk_name);
        $this->assertNotEmpty($document->disk_path);
        $this->assertEquals('test.pdf', $document->title);
        $this->assertEquals('application/pdf', $document->mime);
        $this->assertTrue($document->uploader->is($import->creator));
        $this->assertNull($document->team);
        // $this->assertTrue($document->team->is($user->currentTeam));
        $this->assertNotNull($document->disk_path);

        $this->assertNotNull($importDocument->fresh()->processed_at);

        Storage::disk(Disk::DOCUMENTS->value)->assertExists($document->disk_path);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);

        Queue::assertPushed(ExtractDocumentProperties::class);
    }
}
