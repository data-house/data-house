<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\GeographicRegion;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PrinsFrank\Standards\Country\CountryAlpha3;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_required(): void
    {
        $response = $this->get('/projects');

        $response->assertRedirectToRoute('login');
    }

    public function test_projects_can_be_listed(): void
    {
        $user = User::factory()->manager()->create();

        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->get('/projects');

        $response->assertSuccessful();

        $response->assertViewHas('projects');
        $response->assertViewHas('searchQuery', null);

        $actualProject = $response->viewData('projects')->first();

        $this->assertTrue($actualProject->is($project));

        $response->assertSee('Germany');
        $response->assertSee('Europe');
    }

    public function test_projects_can_be_searched(): void
    {
        $user = User::factory()->manager()->create();

        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->get('/projects?s=keyword');

        $response->assertSuccessful();

        $response->assertViewHas('projects');
        $response->assertViewHas('searchQuery', 'keyword');

        $actualProject = $response->viewData('projects')->first();

        $this->assertNull($actualProject);
    }


    public function test_projects_is_viewable(): void
    {
        $user = User::factory()->manager()->create();

        $project = Project::factory()
            ->has(Document::factory()->count(3))
            ->create([
                'type' => ProjectType::BILATERAL,
                'countries' => [CountryAlpha3::Costa_Rica],
            ]);

        $response = $this->actingAs($user)
            ->get('/projects/' . $project->ulid);

        $response->assertSuccessful();
        $response->assertViewHas('project', $project);
        $response->assertViewHas('documents', $project->documents);
        $response->assertSee('Costa Rica');
        $response->assertSee('Latin America and the Caribbean');
    }
}
