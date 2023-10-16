<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ImportFileDataJob;
use App\Jobs\RetrieveDocumentsToImportJob;
use App\Models\Disk;
use App\Models\Import;
use App\Models\ImportDocument;
use App\Models\ImportDocumentStatus;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RetrieveDocumentsToImportJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_map_can_be_processed(): void
    {
        Storage::fake(Disk::IMPORTS->value);

        $fakeWebdavDisk = Storage::fake('webdav');

        Storage::shouldReceive('build')->with([
            'driver' => 'webdav',
            'url' => 'http://service/',
            'user' => 'fake-disk-user',
            'password' => 'fake-disk-password',
        ])->andReturn($fakeWebdavDisk);

        $fakeWebdavDisk->putFileAs('folder-with-documents', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        Queue::fake();

        $import = Import::factory()->create([
            'status' => ImportStatus::RUNNING,
            'configuration' => [
                "url" => "http://service/",
                "user" => "fake-disk-user",
                "password" => "fake-disk-password",
            ],
        ]);

        $importMap = $import->maps()->create([
            'status' => ImportStatus::RUNNING,
            'mapped_team' => null,
            'mapped_uploader' => $import->creator->getKey(),
            'recursive' => false,
            'filters' => [
                'paths' => "folder-with-documents"
            ],
        ]);

        (new RetrieveDocumentsToImportJob($importMap))->handle();

        $importDocuments = $importMap->documents()->get();

        $this->assertCount(1, $importDocuments);

        $document = $importDocuments->first();

        $this->assertInstanceOf(ImportDocument::class, $document);

        $this->assertEquals('folder-with-documents/test.pdf', $document->source_path);
        $this->assertEquals('application/pdf', $document->mime);
        $this->assertEquals('imports', $document->disk_name);
        $this->assertEquals(ImportDocumentStatus::PENDING, $document->status);
        $this->assertEquals(hash('sha256', 'folder-with-documents/test.pdf'), $document->import_hash);
        $this->assertEquals(70610, $document->document_size);
        $this->assertNotNull($document->document_date);
        $this->assertInstanceOf(Carbon::class, $document->document_date);
        $this->assertNull($document->disk_path);
        $this->assertNull($document->team_id);
        $this->assertEquals($import->creator->getKey(), $document->uploaded_by);

        Queue::assertPushed(ImportFileDataJob::class, function($job) use ($importMap){
            return $job->importMap->is($importMap);
        });
    }

    public function test_import_map_supports_folder_and_files_selected(): void
    {
        Storage::fake(Disk::IMPORTS->value);
        Queue::fake();

        $fakeWebdavDisk = Storage::fake('webdav');

        Storage::shouldReceive('build')->with([
            'driver' => 'webdav',
            'url' => 'http://service/basepath/',
            'user' => 'fake-disk-user',
            'password' => 'fake-disk-password',
        ])->andReturn($fakeWebdavDisk);

        $fakeWebdavDisk->putFileAs('folder-with-documents', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $import = Import::factory()->create([
            'status' => ImportStatus::RUNNING,
            'configuration' => [
                "url" => "http://service/basepath/",
                "user" => "fake-disk-user",
                "password" => "fake-disk-password",
            ],
        ]);

        $importMap = $import->maps()->create([
            'status' => ImportStatus::RUNNING,
            'mapped_team' => null,
            'mapped_uploader' => $import->creator->getKey(),
            'recursive' => false,
            'filters' => [
                'paths' => [
                    "basepath/folder-with-documents",
                    "basepath/folder-with-documents/test.pdf",
                ]
            ],
        ]);

        (new RetrieveDocumentsToImportJob($importMap))->handle();

        $importDocuments = $importMap->documents()->get();

        $this->assertCount(1, $importDocuments);

        $document = $importDocuments->first();

        $this->assertInstanceOf(ImportDocument::class, $document);

        $this->assertEquals('folder-with-documents/test.pdf', $document->source_path);
        $this->assertEquals('application/pdf', $document->mime);
        $this->assertEquals('imports', $document->disk_name);
        $this->assertNull($document->disk_path);
        $this->assertNull($document->team_id);
        $this->assertEquals($import->creator->getKey(), $document->uploaded_by);

        Queue::assertPushed(ImportFileDataJob::class, function($job) use ($importMap){
            return $job->importMap->is($importMap);
        });
    }

}
