<?php

namespace Tests\Feature\Operations;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\QuestionFeedback;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BackfillDocumentTypeTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_document_type_updated(): void
    {
        $document = Document::factory()->create([
            'type' => null,
        ]);

        $this->artisan('operations:process 2023_08_08_115445_backfill_document_type')
            ->assertExitCode(0);

        $updatedDocument = $document->fresh();

        $this->assertNotNull($updatedDocument->type);

        $this->assertEquals(DocumentType::DOCUMENT, $updatedDocument->type);
    }
    
    public function test_already_classified_documents_not_updated(): void
    {
        $document = Document::factory()->create([
            'type' => DocumentType::PROJECT_REPORT,
        ]);

        $this->artisan('operations:process 2023_08_08_115445_backfill_document_type')
            ->assertExitCode(0);

        $updatedDocument = $document->fresh();

        $this->assertEquals(DocumentType::PROJECT_REPORT, $updatedDocument->type);
        $this->assertEquals($document->updated_at, $updatedDocument->updated_at);
    }
}
