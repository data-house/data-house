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
use Illuminate\Http\File;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackfillDocumentHasTextualContentPropertyTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_has_textual_content_updated(): void
    {
        Storage::fake();

        Event::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
                'properties' => ['initial' => 'value'],
            ]);

        $this->artisan('operations:process 2024_05_17_165928_backfill_has_textual_content_document_property')
            ->assertExitCode(0);

        $updatedDocument = $document->fresh();

        $this->assertEquals('value', $updatedDocument->properties['initial']);
        $this->assertTrue($updatedDocument->properties['has_textual_content'] ?? false);
    }
}
