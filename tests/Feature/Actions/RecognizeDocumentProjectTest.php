<?php

namespace Tests\Feature\Actions;

use App\Actions\RecognizeDocumentProject;
use App\Models\Document;
use App\Models\ImportDocument;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RecognizeDocumentProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_null_returned_when_projects_cannot_be_recognized(): void
    {
        $document = Document::factory()->create([
            'title' => 'A generic title'
        ]);

        $action = new RecognizeDocumentProject();

        $project = $action($document);

        $this->assertNull($project);
    }

    public function test_null_returned_when_project_cannot_be_recognized_using_import_source(): void
    {
        $expectedProject = Project::factory()->create([
            'slug' => 'project-to-match'
        ]);

        $document = Document::factory()
            ->has(ImportDocument::factory()->state([
                'source_path' => 'path/test.pdf'
            ]), 'importDocument')
            ->create([
                'title' => 'A generic title'
            ]);

        $action = new RecognizeDocumentProject();

        $project = $action($document);

        $this->assertNull($project);
    }

    public function test_project_recognized_using_import_source(): void
    {
        $expectedProject = Project::factory()->create([
            'slug' => 'project-to-match'
        ]);

        $document = Document::factory()
            ->has(ImportDocument::factory()->state([
                'source_path' => 'path [project-to-match]/test.pdf'
            ]), 'importDocument')
            ->create([
                'title' => 'A generic title'
            ]);

        $action = new RecognizeDocumentProject();

        $project = $action($document);

        $this->assertInstanceOf(Project::class, $project);

        $this->assertTrue($project->is($expectedProject));
    }
    
    public function test_project_recognized_when_slug_has_spaces(): void
    {
        $expectedProject = Project::factory()->create([
            'slug' => 'project to-match'
        ]);

        $document = Document::factory()
            ->has(ImportDocument::factory()->state([
                'source_path' => 'path [project to-match]/test.pdf'
            ]), 'importDocument')
            ->create([
                'title' => 'A generic title'
            ]);

        $action = new RecognizeDocumentProject();

        $project = $action($document);

        $this->assertInstanceOf(Project::class, $project);

        $this->assertTrue($project->is($expectedProject));
    }

    public function test_multiple_brackets_handled(): void
    {
        $expectedProject = Project::factory()->create([
            'slug' => 'project-to-match'
        ]);

        $document = Document::factory()
            ->has(ImportDocument::factory()->state([
                'source_path' => 'path [project-to-match]/other folder [other-slug]/test.pdf'
            ]), 'importDocument')
            ->create([
                'title' => 'A generic title'
            ]);

        $action = new RecognizeDocumentProject();

        $project = $action($document);

        $this->assertInstanceOf(Project::class, $project);

        $this->assertTrue($project->is($expectedProject));
    }
}
