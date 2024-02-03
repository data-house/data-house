<?php

namespace Tests\Feature\Jobs\Pipeline;

use App\Actions\RecognizeDocumentProject;
use App\Jobs\Pipeline\Document\LinkDocumentWithAProject;
use App\Models\Document;
use App\Models\ImportDocument;
use App\Models\Project;
use App\Pipelines\Pipeline;
use App\Pipelines\PipelineTrigger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LinkDocumentWithAProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_execution_on_direct_uploaded_document(): void
    {
        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create([
                'title' => 'A generic title'
            ]);

        $job = new LinkDocumentWithAProject($model, $model->latestPipelineRun);

        $job->handle(app()->make(RecognizeDocumentProject::class));

        $document = $model->fresh();

        $this->assertNull($document->project);
    }

    public function test_execution_on_imported_document(): void
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

        $job = new LinkDocumentWithAProject($model, $model->latestPipelineRun);

        $job->handle(app()->make(RecognizeDocumentProject::class));

        $document = $model->fresh();

        $this->assertTrue($document->project->is($expectedProject));
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

        $job = new LinkDocumentWithAProject($expectedProject, $model->latestPipelineRun);

        $this->expectNotToPerformAssertions();

        $job->handle(app()->make(RecognizeDocumentProject::class));
    }
}
