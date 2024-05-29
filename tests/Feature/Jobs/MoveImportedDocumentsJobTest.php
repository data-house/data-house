<?php

namespace Tests\Feature\Jobs;

use App\Jobs\MoveImportedDocumentsJob;
use App\Jobs\Pipeline\Document\ExtractDocumentProperties;
use App\Jobs\Pipeline\Document\LinkDocumentWithAProject;
use App\Models\Disk;
use App\Models\Document;
use App\Models\Import;
use App\Models\ImportDocument;
use App\Models\ImportDocumentStatus;
use App\Models\ImportSource;
use App\Models\ImportStatus;
use App\Models\Team;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
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
            'team_id' => null,
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'import_hash' => hash('sha256', 'test.pdf'),
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $document = Document::first();

        $this->assertNotNull($document->ulid);
        $this->assertEquals('documents', $document->disk_name);
        $this->assertNotEmpty($document->disk_path);
        $this->assertEquals('test.pdf', $document->title);
        $this->assertEquals('application/pdf', $document->mime);
        $this->assertTrue($document->uploader->is($import->creator));
        $this->assertNull($document->team);
        $this->assertNotNull($document->disk_path);

        $this->assertEquals($importDocument->document_hash, $document->document_hash);
        $this->assertEquals(70610, $document->document_size);
        $this->assertNotNull($document->document_date);
        $this->assertTrue($document->document_date->isToday());
        $this->assertEquals(Visibility::TEAM, $document->visibility);
        $this->assertArrayHasKey('filename', $document->properties);
        $this->assertEquals('test.pdf', $document->properties['filename']);

        $updatedImportDocument = $importDocument->fresh();
        $this->assertNotNull($updatedImportDocument->processed_at);
        $this->assertEquals(ImportDocumentStatus::COMPLETED, $updatedImportDocument->status);
        
        $this->assertNotNull($updatedImportDocument->document_id);
        $this->assertTrue($updatedImportDocument->document->is($document));
        $this->assertTrue($document->importDocument->is($updatedImportDocument));

        Storage::disk(Disk::DOCUMENTS->value)->assertExists($document->disk_path);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertNotNull($importMap->fresh()->last_executed_at);
        $this->assertNotNull($importMap->fresh()->last_session_completed_at);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);

        Queue::assertPushed(LinkDocumentWithAProject::class);

        $this->assertEquals('activity.import-map-completed', Activity::all()->first()->description);
    }

    public function test_imported_document_respect_default_visibility(): void
    {
        config(['library.default_document_visibility' => 'protected']);

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
            'team_id' => null,
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'import_hash' => hash('sha256', 'test.pdf'),
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $document = Document::first();

        $this->assertEquals('documents', $document->disk_name);
        $this->assertNotEmpty($document->disk_path);
        $this->assertEquals('test.pdf', $document->title);
        $this->assertEquals('application/pdf', $document->mime);
        $this->assertTrue($document->uploader->is($import->creator));
        $this->assertNull($document->team);
        $this->assertNotNull($document->disk_path);

        $this->assertEquals($importDocument->document_hash, $document->document_hash);
        $this->assertEquals(70610, $document->document_size);
        $this->assertNotNull($document->document_date);
        $this->assertTrue($document->document_date->isToday());
        $this->assertEquals(Visibility::PROTECTED, $document->visibility);

        $updatedImportDocument = $importDocument->fresh();
        $this->assertNotNull($updatedImportDocument->processed_at);
        $this->assertEquals(ImportDocumentStatus::COMPLETED, $updatedImportDocument->status);
        
        $this->assertNotNull($updatedImportDocument->document_id);
        $this->assertTrue($updatedImportDocument->document->is($document));
        $this->assertTrue($document->importDocument->is($updatedImportDocument));

        Storage::disk(Disk::DOCUMENTS->value)->assertExists($document->disk_path);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);

        Queue::assertPushed(LinkDocumentWithAProject::class);
    }
    
    public function test_imported_document_respect_visibility_defined_in_import_map(): void
    {
        config(['library.default_document_visibility' => 'team']);

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
            'visibility' => Visibility::PROTECTED,
        ]);

        $importDocument = $importMap->documents()->create([
            'source_path' => "test.pdf",
            'disk_name' => "imports",
            'disk_path' => 'test.pdf',
            'mime' => "application/pdf",
            'uploaded_by' => $import->creator->getKey(),
            'team_id' => null,
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'import_hash' => hash('sha256', 'test.pdf'),
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $document = Document::first();

        $this->assertEquals('documents', $document->disk_name);
        $this->assertNotEmpty($document->disk_path);
        $this->assertEquals('test.pdf', $document->title);
        $this->assertEquals('application/pdf', $document->mime);
        $this->assertTrue($document->uploader->is($import->creator));
        $this->assertNull($document->team);
        $this->assertNotNull($document->disk_path);

        $this->assertEquals($importDocument->document_hash, $document->document_hash);
        $this->assertEquals(70610, $document->document_size);
        $this->assertNotNull($document->document_date);
        $this->assertTrue($document->document_date->isToday());
        $this->assertEquals(Visibility::PROTECTED, $document->visibility);

        $updatedImportDocument = $importDocument->fresh();
        $this->assertNotNull($updatedImportDocument->processed_at);
        $this->assertEquals(ImportDocumentStatus::COMPLETED, $updatedImportDocument->status);
        
        $this->assertNotNull($updatedImportDocument->document_id);
        $this->assertTrue($updatedImportDocument->document->is($document));
        $this->assertTrue($document->importDocument->is($updatedImportDocument));

        Storage::disk(Disk::DOCUMENTS->value)->assertExists($document->disk_path);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);

        Queue::assertPushed(LinkDocumentWithAProject::class);
    }

    public function test_different_hash_after_transfer_raises_failure(): void
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
            'team_id' => null,
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => 'fake-hash-to-simulate-transfer-error',
            'import_hash' => hash('sha256', 'test.pdf'),
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $document = Document::first();

        $this->assertNull($document);

        $updatedImportDocument = $importDocument->fresh();
        $this->assertNotNull($updatedImportDocument->processed_at);
        $this->assertEquals(ImportDocumentStatus::FAILED, $updatedImportDocument->status);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);

        Queue::assertNothingPushed();
    }

    public function test_skipping_possible_duplicate_in_user_teams(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);
        
        $fakeDocumentDisk = Storage::fake(Disk::DOCUMENTS->value);

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');
        
        $fakeDocumentDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $user = User::factory()->manager()->withPersonalTeam()->create();

        $document = Document::factory()->create([
            'document_hash' => $fakeDocumentDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'uploaded_by' => $user->getKey(),
            'team_id' => $user->personalTeam()->getKey(),
        ]);

        Queue::fake();

        $import = Import::factory()->create([
            'source' => ImportSource::LOCAL,
            'status' => ImportStatus::RUNNING,
            'created_by' => $user->getKey(),
            'configuration' => [
                'root' => $fakeImportDisk->path(''), // only used to make the import configuration looks correct
            ],
        ]);

        $importMap = $import->maps()->create([
            'status' => ImportStatus::RUNNING,
            'mapped_team' => null,
            'mapped_uploader' => $user->getKey(),
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
            'uploaded_by' => $user->getKey(),
            'team_id' => null, // the test ensure that duplicate check uses all user accessible teams
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'import_hash' => hash('sha256', 'test.pdf'),
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $this->assertTrue(Document::first()->is($document));
        $this->assertEquals(1, Document::count());

        $updatedImportDocument = $importDocument->fresh();
        $this->assertNotNull($updatedImportDocument->processed_at);
        $this->assertEquals(ImportDocumentStatus::SKIPPED_DUPLICATE, $updatedImportDocument->status);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);

        Queue::assertNothingPushed();
    }

    public function test_previous_duplicates_are_skipped(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);
        
        $fakeDocumentDisk = Storage::fake(Disk::DOCUMENTS->value);

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');
        
        $fakeDocumentDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $user = User::factory()->manager()->withPersonalTeam()->create();

        Queue::fake();

        $import = Import::factory()->create([
            'source' => ImportSource::LOCAL,
            'status' => ImportStatus::RUNNING,
            'created_by' => $user->getKey(),
            'configuration' => [
                'root' => $fakeImportDisk->path(''), // only used to make the import configuration looks correct
            ],
        ]);

        $importMap = $import->maps()->create([
            'status' => ImportStatus::RUNNING,
            'mapped_team' => null,
            'mapped_uploader' => $user->getKey(),
            'recursive' => false,
            'filters' => [
                'paths' => "test.pdf"
            ],
        ]);

        $importDocumentLeftoverByTerminatedJob = ImportDocument::factory()->recycle($importMap)->create([
            'source_path' => "test.pdf",
            'disk_name' => "imports",
            'disk_path' => 'test.pdf',
            'mime' => "application/pdf",
            'uploaded_by' => $user->getKey(),
            'team_id' => null, // the test ensure that duplicate check uses all user accessible teams
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'import_hash' => hash('sha256', 'test.pdf'),
            'status' => ImportDocumentStatus::IMPORTING,
            'processed_at' => null,
        ]);

        $importDocumentPreviouslySkipped = ImportDocument::factory()->recycle($importMap)->create([
            'source_path' => "test.pdf",
            'disk_name' => "imports",
            'disk_path' => 'test.pdf',
            'mime' => "application/pdf",
            'uploaded_by' => $user->getKey(),
            'team_id' => null, // the test ensure that duplicate check uses all user accessible teams
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'import_hash' => hash('sha256', 'test.pdf'),
            'status' => ImportDocumentStatus::SKIPPED_DUPLICATE,
            'processed_at' => null,
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $updatedImportDocument = $importDocumentLeftoverByTerminatedJob->fresh();
        $this->assertNotNull($updatedImportDocument->processed_at);
        $this->assertEquals(ImportDocumentStatus::COMPLETED, $updatedImportDocument->status);
        
        $previouslySkipped = $importDocumentPreviouslySkipped->fresh();
        $this->assertNull($previouslySkipped->processed_at);
        $this->assertEquals(ImportDocumentStatus::SKIPPED_DUPLICATE, $previouslySkipped->status);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);
    }

    public function test_move_imported_documents_cancelled_if_user_does_not_have_permissions_anymore(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);
        
        $fakeDocumentDisk = Storage::fake(Disk::DOCUMENTS->value);

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');
        
        $fakeDocumentDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $user = User::factory()->manager()->withPersonalTeam()->create();

        $document = Document::factory()->create([
            'document_hash' => $fakeDocumentDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'uploaded_by' => $user->getKey(),
            'team_id' => $user->personalTeam()->getKey(),
        ]);

        Queue::fake();

        $import = Import::factory()->create([
            'source' => ImportSource::LOCAL,
            'status' => ImportStatus::RUNNING,
            'created_by' => $user->getKey(),
            'configuration' => [
                'root' => $fakeImportDisk->path(''), // only used to make the import configuration looks correct
            ],
        ]);

        $team = Team::factory()->create();

        $importMap = $import->maps()->create([
            'status' => ImportStatus::RUNNING,
            'mapped_team' =>null,
            'mapped_uploader' => $user->getKey(),
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
            'uploaded_by' => $user->getKey(),
            'team_id' => $team->getKey(), // current user do not have access to this team
            'document_size' => 70610,
            'document_date' => today(),
            'document_hash' => $fakeImportDisk->checksum('test.pdf', ['checksum_algo' => 'sha256']),
            'import_hash' => hash('sha256', 'test.pdf'),
        ]);

        (new MoveImportedDocumentsJob($importMap))->handle();

        $this->assertTrue(Document::first()->is($document));
        $this->assertEquals(1, Document::count());

        $updatedImportDocument = $importDocument->fresh();
        $this->assertNotNull($updatedImportDocument->processed_at);
        $this->assertEquals(ImportDocumentStatus::CANCELLED_MISSING_PERMISSION, $updatedImportDocument->status);

        $this->assertEquals(ImportStatus::COMPLETED, $importMap->fresh()->status);
        $this->assertEquals(ImportStatus::COMPLETED, $import->fresh()->status);

        Queue::assertNothingPushed();
    }
}
