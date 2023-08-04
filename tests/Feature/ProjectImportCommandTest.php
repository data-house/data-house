<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Project;
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
                "description": "Project Description"
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
        
        $this->assertEquals('Importiertes Projekt', $projects->first()->title);
        $this->assertEquals('Imported Project', $projects->first()->properties['title_en']);
        $this->assertEquals([
            "Some organization"
        ], $projects->first()->organizations['implementers']);

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
