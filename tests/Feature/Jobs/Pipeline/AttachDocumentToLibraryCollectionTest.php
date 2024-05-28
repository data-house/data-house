<?php

namespace Tests\Feature\Jobs\Pipeline;

use App\Actions\MatchDocumentCollections;
use App\Jobs\Pipeline\Document\AttachDocumentToLibraryCollection;
use App\Models\Collection;
use App\Models\Document;
use App\Models\ImportDocument;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttachDocumentToLibraryCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execution_on_direct_uploaded_document(): void
    {
        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'title' => 'A generic title'
            ]);

        $job = new AttachDocumentToLibraryCollection($model, $model->latestPipelineRun);

        $job->handle(app()->make(MatchDocumentCollections::class));

        $document = $model->fresh();

        $this->assertEmpty($document->collections);
    }

    public function test_team_only_documents_are_not_processed(): void
    {
        $model = Document::factory()
            ->visibleByTeamMembers()
            ->hasPipelineRuns(1)
            ->has(ImportDocument::factory()->state([
                'source_path' => 'path [t:reports-and-studies]/test.pdf'
            ]), 'importDocument')
            ->create([
                'title' => 'A generic title'
            ]);

        $job = new AttachDocumentToLibraryCollection($model, $model->latestPipelineRun);

        $job->handle(app()->make(MatchDocumentCollections::class));

        $document = $model->fresh();

        $this->assertEmpty($document->collections);
    }

    public function test_document_attached_to_collection(): void
    {
        $expectedCollection = Collection::factory()->library()->create([
            'title' => "Reports and Studies",
            'topic_name' => 'reports-and-studies',
        ]);

        $model = Document::factory()
            ->visibleByAnyUser()
            ->hasPipelineRuns(1)
            ->has(ImportDocument::factory()->state([
                'source_path' => 'path [t:reports-and-studies]/test.pdf'
            ]), 'importDocument')
            ->create([
                'title' => 'A generic title'
            ]);

        $job = new AttachDocumentToLibraryCollection($model, $model->latestPipelineRun);

        $job->handle(app()->make(MatchDocumentCollections::class));

        $document = $model->fresh();

        $this->assertTrue($document->collections()->first()->is($expectedCollection));
    }

    public function test_execution_performed_only_on_document_models(): void
    {
        $expectedProject = Project::factory()->create([
            'slug' => 'project-to-match'
        ]);

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->has(ImportDocument::factory()->state([
                'source_path' => 'path [project-to-match]/test.pdf'
            ]), 'importDocument')
            ->create([
                'title' => 'A generic title'
            ]);

        $job = new AttachDocumentToLibraryCollection($expectedProject, $model->latestPipelineRun);

        $this->expectNotToPerformAssertions();

        $job->handle(app()->make(MatchDocumentCollections::class));
    }
}
