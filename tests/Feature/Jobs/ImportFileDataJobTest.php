<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ImportFileDataJob;
use App\Jobs\MoveImportedDocumentsJob;
use App\Jobs\RetrieveDocumentsToImportJob;
use App\Models\Disk;
use App\Models\Import;
use App\Models\ImportDocument;
use App\Models\ImportDocumentStatus;
use App\Models\ImportMap;
use App\Models\ImportSource;
use App\Models\ImportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportFileDataJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_can_be_downloaded(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeWebdavDisk = Storage::fake('ondemand');

        $fakeWebdavDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');


        $import = Import::factory()->create([
            'source' => ImportSource::LOCAL,
            'status' => ImportStatus::RUNNING,
            'configuration' => [
                'root' => $fakeWebdavDisk->path(''), // using the path method to get the root of the ondemand disk that will point to the fake disk
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
            'disk_path' => null,
            'mime' => "application/pdf",
            'uploaded_by' => $import->creator->getKey(),
            'team_id' => null
        ]);

        (new ImportFileDataJob($importMap))->handle();

        $imported = $importDocument->fresh();

        $this->assertNotNull($imported->disk_path);
        $this->assertStringStartsWith("{$importMap->getKey()}", $imported->disk_path);
        $this->assertNotNull($imported->retrieved_at);
        $this->assertNull($imported->processed_at);
        $this->assertEquals(ImportDocumentStatus::IMPORTING, $imported->status);
        $this->assertEquals(Storage::disk(Disk::IMPORTS->value)->checksum($imported->disk_path, ['checksum_algo' => 'sha256']), $imported->document_hash);

        Storage::disk(Disk::IMPORTS->value)->assertExists($imported->disk_path);

        Queue::assertPushed(MoveImportedDocumentsJob::class, function($job) use ($importMap) {
            return $job->importMap->is($importMap);
        });
    }
    
    public function test_download_skipped_if_file_does_not_exists(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeWebdavDisk = Storage::fake('ondemand');

        $fakeWebdavDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');


        $import = Import::factory()->create([
            'source' => ImportSource::LOCAL,
            'status' => ImportStatus::RUNNING,
            'configuration' => [
                'root' => $fakeWebdavDisk->path(''), // using the path method to get the root of the ondemand disk that will point to the fake disk
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
            'disk_path' => null,
            'mime' => "application/pdf",
            'uploaded_by' => $import->creator->getKey(),
            'team_id' => null
        ]);

        $fakeWebdavDisk->delete('test.pdf');

        (new ImportFileDataJob($importMap))->handle();

        $imported = $importDocument->fresh();

        $this->assertNull($imported->disk_path);
        $this->assertNull($imported->retrieved_at);
        $this->assertNull($imported->processed_at);
        $this->assertEquals(ImportDocumentStatus::SKIPPED_MISSING_SOURCE, $imported->status);
        $this->assertNull($imported->document_hash);

        Storage::disk(Disk::IMPORTS->value)->assertExists($imported->disk_path);

        Queue::assertPushed(MoveImportedDocumentsJob::class, function($job) use ($importMap) {
            return $job->importMap->is($importMap);
        });
    }
}
