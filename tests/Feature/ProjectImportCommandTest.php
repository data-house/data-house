<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_imported_from_json(): void
    {
        Storage::fake('local');


        $json = <<<'json'
        [
            {
                "title": {
                    "en": "Imported Project",
                    "de": "Importiertes Projekt"
                },
                "topics": [
                    "energy efficiency"
                ],
                "type": 30,
                "countries": [
                    "PRY",
                    "VNM"
                ],
                "organizations": {
                    "implementers": [
                        "Some organization"
                    ]
                },
                "properties": null,
                "slug": "assigned-slug",
                "description": "Project Description",
                "status": 20,
                "starts_at": "2008-12-15 00:00:00",
                "ends_at": "2012-08-31 00:00:00",
                "iki-funding": 3650000.56
            }
        ]
        json;

        Storage::disk('local')->put('projects.json', $json);

        $this->artisan('project:import', [
                'file' => 'projects.json',
            ])
            ->assertSuccessful();

        $projects = Project::query()->get();

        $this->assertCount(1, $projects);

        $project = $projects->first();
        
        $this->assertEquals('Importiertes Projekt', $project->title);
        $this->assertEquals('Imported Project', $project->properties['title_en']);
        $this->assertEquals(ProjectStatus::COMPLETED, $project->status);
        $this->assertEquals('2008-12-15', $project->starts_at->toDateString());
        $this->assertEquals('2012-08-31', $project->ends_at->toDateString());
        $this->assertEquals(3650000.56, $project->funding['iki']);
        $this->assertEquals([
            "Some organization"
        ], $project->organizations['implementers']);

    }

    public function test_existing_documents_linked_when_importing_projects(): void
    {
        Storage::fake('local');

        $document = Document::factory()->create([
            'title' => 'document-title.pdf',
        ]);

        $json = <<<'json'
        [
            {
                "title": {
                    "en": "Imported Project",
                    "de": "Importiertes Projekt"
                },
                "topics": [
                    "energy efficiency"
                ],
                "type": 30,
                "countries": [
                    "PRY",
                    "VNM"
                ],
                "organizations": {
                    "implementers": [
                        "Some organization"
                    ]
                },
                "properties": null,
                "slug": "assigned-slug",
                "description": "Project Description",
                "documents": "document-title.pdf"
            }
        ]
        json;


        Storage::disk('local')->put('projects.json', $json);

        $this->artisan('project:import', [
                'file' => 'projects.json',
            ])
            ->assertSuccessful();

        $projects = Project::query()->with('documents')->get();

        $this->assertCount(1, $projects);
        
        $this->assertEquals('Importiertes Projekt', $projects->first()->title);
        $this->assertEquals('Imported Project', $projects->first()->properties['title_en']);
        $this->assertEquals([
            "Some organization"
        ], $projects->first()->organizations['implementers']);

        $this->assertTrue($projects->first()->documents->first()->is($document));

    }
}
