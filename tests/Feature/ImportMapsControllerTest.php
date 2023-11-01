<?php

namespace Tests\Feature;

use App\Models\Import;
use App\Models\ImportDocument;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ImportMapsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mapping_page_requires_authentication(): void
    {
        $mapping = ImportMap::factory()->create();

        $response = $this->get("/mappings/{$mapping->ulid}");

        $response->assertRedirectToRoute('login');
    }

    public function test_mapping_page_requires_authorization(): void
    {
        $user = User::factory()->guest()->create();

        $mapping = ImportMap::factory()->create();

        $response = $this->actingAs($user)
            ->get("/mappings/{$mapping->ulid}");

        $response->assertForbidden();
    }

    public function test_mapping_documents_can_be_listed(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $importDoc = ImportDocument::factory()
            ->recycle($user)
            ->create([
                'uploaded_by' => $user->getKey(),
                'team_id' => $user->currentTeam->getKey(),
            ]);
        
        $mapping = $importDoc->importMap;

        $response = $this
            ->actingAs($user)
            ->get("/mappings/{$mapping->ulid}");

        $response->assertStatus(200);

        $response->assertViewIs('import-map.show');

        $response->assertViewHas('import', Import::first());
        $response->assertViewHas('mapping', $mapping);
        $response->assertViewHas('documents', ImportDocument::all());

        $response->assertSeeInOrder(['Status', ImportStatus::CREATED->name]);
    }

    public function test_no_documents_handled(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $mapping = ImportMap::factory()
            ->recycle($user)
            ->create();

        $response = $this
            ->actingAs($user)
            ->get("/mappings/{$mapping->ulid}");

        $response->assertStatus(200);

        $response->assertViewIs('import-map.show');

        $response->assertViewHas('import', Import::first());
        $response->assertViewHas('mapping', $mapping);
        $response->assertViewHas('documents', ImportDocument::all());

        $response->assertSeeInOrder(['Status', ImportStatus::CREATED->name]);
    }
}
