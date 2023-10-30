<?php

namespace Tests\Feature\Operations;

use App\Models\Document;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BackfillDocumentVisibilityTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_team_visibility_assigned_to_drafts(): void
    {
        $document = Document::factory()->create([
            'visibility' => null,
            'draft' => true,
        ]);

        $this->artisan('operations:process 2023_10_20_123528_backfill_document_visibility')
            ->assertExitCode(0);

        $updatedDocument = $document->fresh();

        $this->assertNotNull($updatedDocument->visibility);

        $this->assertEquals(Visibility::TEAM, $updatedDocument->visibility);
    }
    
    public function test_protected_visibility_assigned_to_not_drafts(): void
    {
        $document = Document::factory()->create([
            'visibility' => null,
            'draft' => false,
        ]);

        $this->artisan('operations:process 2023_10_20_123528_backfill_document_visibility')
            ->assertExitCode(0);

        $updatedDocument = $document->fresh();

        $this->assertNotNull($updatedDocument->visibility);

        $this->assertEquals(Visibility::PROTECTED, $updatedDocument->visibility);
    }
    
    public function test_documents_with_visibility_set_not_modified(): void
    {
        $document = Document::factory()->create([
            'visibility' => Visibility::PERSONAL,
            'draft' => true,
        ]);

        $this->artisan('operations:process 2023_10_20_123528_backfill_document_visibility')
            ->assertExitCode(0);

        $updatedDocument = $document->fresh();

        $this->assertEquals(Visibility::PERSONAL, $updatedDocument->visibility);

        $this->assertEquals($document->updated_at, $updatedDocument->updated_at);
    }
}
