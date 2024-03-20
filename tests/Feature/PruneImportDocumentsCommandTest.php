<?php

namespace Tests\Feature;

use App\Models\Disk;
use App\Models\ImportDocument;
use App\Models\ImportDocumentStatus;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneImportDocumentsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_documents_pruned(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $importDocuments = ImportDocument::factory()
            ->count(3)
            ->state(new Sequence(
                ['status' => ImportDocumentStatus::COMPLETED, 'created_at' => today()->subHours(25)],
                ['status' => ImportDocumentStatus::PENDING, 'created_at' => today()->subHours(25)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'created_at' => today()->subHours(25)],
            ))
            ->create();
       
        $this->artisan('import:prune-documents')
            ->expectsOutputToContain('Pruned 1 documents')
            ->assertSuccessful();

        $this->assertEquals(2, ImportDocument::count());
        $this->assertEquals(0, ImportDocument::whereStatus(ImportDocumentStatus::SKIPPED_DUPLICATE->value)->count());
        
        Queue::assertNothingPushed();
    }

    public function test_pruned_older_than_hours(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $importDocuments = ImportDocument::factory()
            ->count(4)
            ->state(new Sequence(
                ['status' => ImportDocumentStatus::COMPLETED, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::PENDING, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'processed_at' => now()->subHours(20), 'created_at' => now()->subHours(21)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'created_at' => now()->subHours(19)],
            ))
            ->create();
       
        $this->artisan('import:prune-documents', [
                '--hours' => 20,
            ])
            ->expectsOutputToContain('Pruned 1 documents')
            ->assertSuccessful();

        $this->assertEquals(3, ImportDocument::count());
        
        Queue::assertNothingPushed();
    }

    public function test_prune_command_supports_dry_run(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $importDocuments = ImportDocument::factory()
            ->count(4)
            ->state(new Sequence(
                ['status' => ImportDocumentStatus::COMPLETED, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::PENDING, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'processed_at' => now()->subHours(20), 'created_at' => now()->subHours(21)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'created_at' => now()->subHours(19)],
            ))
            ->create();

        $expectedPrunedImportDocument = $importDocuments[2]->getKey();
       
        $this->artisan('import:prune-documents', [
                '--hours' => 20,
                '--dry-run' => true,
            ])
            ->expectsOutputToContain('Pruning 1 documents...')
            ->expectsOutputToContain("dry run results")
            ->expectsOutputToContain("[$expectedPrunedImportDocument] Duplicate")
            ->doesntExpectOutputToContain('Pruned 1 documents')
            ->assertSuccessful();

        $this->assertEquals(4, ImportDocument::count());
        
        Queue::assertNothingPushed();
    }

    public function test_prune_command_clear_dangling_imports_when_dry_run(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $importDocuments = ImportDocument::factory()
            ->count(4)
            ->state(new Sequence(
                ['status' => ImportDocumentStatus::COMPLETED, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::PENDING, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'processed_at' => now()->subHours(20), 'created_at' => now()->subHours(21)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'created_at' => now()->subHours(19)],
            ))
            ->create();

        
       
        $this->artisan('import:prune-documents', [
                '--hours' => 20,
                '--dry-run' => true,
                '--dangling' => true,
            ])
            ->expectsOutputToContain('Pruning 3 documents...')
            ->expectsOutputToContain("dry run results")
            ->expectsOutputToContain("[".$importDocuments[0]->getKey()."] Completed")
            ->expectsOutputToContain("[".$importDocuments[1]->getKey()."] Pending")
            ->expectsOutputToContain("[".$importDocuments[2]->getKey()."] Duplicate")
            ->doesntExpectOutputToContain('Pruned 1 documents')
            ->assertSuccessful();

        $this->assertEquals(4, ImportDocument::count());
        
        Queue::assertNothingPushed();
    }
    
    public function test_prune_command_clear_dangling_imports(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $importDocuments = ImportDocument::factory()
            ->count(4)
            ->state(new Sequence(
                ['status' => ImportDocumentStatus::COMPLETED, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::PENDING, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'processed_at' => now()->subHours(20), 'created_at' => now()->subHours(21)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'created_at' => now()->subHours(19)],
            ))
            ->create();

        $expectedSurvivingImportDocument = $importDocuments[3];
       
        $this->artisan('import:prune-documents', [
                '--hours' => 20,
                '--dangling' => true,
            ])
            ->expectsQuestion('You\'re about to prune dangling documents. This might have undesidered effects to running imports.', 'yes')
            ->expectsOutputToContain('Pruning 3 documents...')
            ->expectsOutputToContain('Pruned 3 documents')
            ->assertSuccessful();

        $this->assertEquals(1, ImportDocument::count());

        $this->assertNotNull($expectedSurvivingImportDocument->fresh());
        
        Queue::assertNothingPushed();
    }
    
    public function test_prune_command_clear_dangling_imports_aborted(): void
    {
        $fakeImportDisk = Storage::fake(Disk::IMPORTS->value);

        Queue::fake();

        $fakeImportDisk->putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $importDocuments = ImportDocument::factory()
            ->count(4)
            ->state(new Sequence(
                ['status' => ImportDocumentStatus::COMPLETED, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::PENDING, 'created_at' => now()->subHours(25)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'processed_at' => now()->subHours(20), 'created_at' => now()->subHours(21)],
                ['status' => ImportDocumentStatus::SKIPPED_DUPLICATE, 'created_at' => now()->subHours(19)],
            ))
            ->create();
       
        $this->artisan('import:prune-documents', [
                '--hours' => 20,
                '--dangling' => true,
            ])
            ->expectsQuestion('You\'re about to prune dangling documents. This might have undesidered effects to running imports.', 'no')
            ->expectsOutputToContain('Pruning 3 documents...')
            ->expectsOutputToContain('Execution aborted')
            ->assertSuccessful();

        $this->assertEquals(4, ImportDocument::count());
        
        Queue::assertNothingPushed();
    }
}
