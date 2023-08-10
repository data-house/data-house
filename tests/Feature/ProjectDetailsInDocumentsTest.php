<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\GeographicRegion;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Jetstream\Jetstream;
use PrinsFrank\Standards\Country\CountryAlpha3;
use Tests\TestCase;

class ProjectDetailsInDocumentsTest extends TestCase
{
    use RefreshDatabase;


    public function test_projects_details_shown_while_viewing_document(): void
    {
        $user = User::factory()->manager()->create();

        $document = Document::factory()
            ->for(Project::factory())
            ->create();

        $response = $this->actingAs($user)
            ->get('/documents/' . $document->ulid);

        $response->assertSuccessful();
        $response->assertViewHas('document', $document);
        $response->assertSee('Germany');
    }
}
