<?php

namespace Tests\Feature;

use App\Data\ImportScheduleSettings;
use App\Models\Import;
use App\Models\ImportDocument;
use App\Models\ImportMap;
use App\Models\ImportSchedule;
use App\Models\ImportStatus;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
    
    public function test_mapping_creation_requires_authorization(): void
    {
        $user = User::factory()->guest()->create();

        $import = Import::factory()
            ->create();

        $response = $this->actingAs($user)
            ->get("imports/{$import->ulid}/mappings/create");

        $response->assertForbidden();
    }

    public function test_mapping_edit_requires_authorization(): void
    {
        $user = User::factory()->guest()->create();

        $mapping = ImportMap::factory()->create();

        $response = $this->actingAs($user)
            ->get("/mappings/{$mapping->ulid}/edit");

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
        $response->assertViewHas('documents');
        $documents = $response->viewData('documents');

        $this->assertInstanceOf(LengthAwarePaginator::class, $documents);
        $this->assertTrue($documents->first()->is(ImportDocument::first()));

        $response->assertSee(ImportStatus::CREATED->label());
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
        $response->assertViewHas('documents');
        $documents = $response->viewData('documents');

        $this->assertInstanceOf(LengthAwarePaginator::class, $documents);
        $this->assertEquals(0, $documents->total());

        $response->assertSee(ImportStatus::CREATED->label());
    }

    public function test_import_map_created(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $response = $this
            ->actingAs($user)
            ->post("/imports/{$import->ulid}/mappings/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'paths' => ['folder'],
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirectToRoute('imports.show', $import);

        $mapping = $import->maps()->first();

        $this->assertInstanceOf(ImportMap::class, $mapping);

        $this->assertTrue($mapping->import->is($import));
        $this->assertTrue($mapping->mappedTeam->is($user->currentTeam));
        $this->assertTrue($mapping->mappedUploader->is($user));
        $this->assertEquals(Visibility::TEAM, $mapping->visibility);
        $this->assertEquals(ImportStatus::CREATED, $mapping->status);
        $this->assertEquals(['paths' => ['folder']], $mapping->filters);
        $this->assertInstanceOf(ImportScheduleSettings::class, $mapping->schedule);
        $this->assertTrue($mapping->schedule->is(ImportSchedule::NOT_SCHEDULED));
    }

    public function test_import_map_created_with_default_visibility(): void
    {
        config(['library.default_document_visibility' => 'protected']);

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $response = $this
            ->actingAs($user)
            ->post("/imports/{$import->ulid}/mappings/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'paths' => ['folder'],
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirectToRoute('imports.show', $import);

        $mapping = $import->maps()->first();

        $this->assertInstanceOf(ImportMap::class, $mapping);

        $this->assertTrue($mapping->import->is($import));
        $this->assertTrue($mapping->mappedTeam->is($user->currentTeam));
        $this->assertTrue($mapping->mappedUploader->is($user));
        $this->assertEquals(Visibility::PROTECTED, $mapping->visibility);
        $this->assertEquals(ImportStatus::CREATED, $mapping->status);
        $this->assertEquals(['paths' => ['folder']], $mapping->filters);
    }

    public function test_import_map_created_with_specific_visibility(): void
    {
        config(['library.default_document_visibility' => 'team']);

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $response = $this
            ->actingAs($user)
            ->post("/imports/{$import->ulid}/mappings/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'paths' => ['folder'],
                'visibility' => Visibility::PROTECTED->value,
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirectToRoute('imports.show', $import);

        $mapping = $import->maps()->first();

        $this->assertInstanceOf(ImportMap::class, $mapping);

        $this->assertTrue($mapping->import->is($import));
        $this->assertTrue($mapping->mappedTeam->is($user->currentTeam));
        $this->assertTrue($mapping->mappedUploader->is($user));
        $this->assertEquals(Visibility::PROTECTED, $mapping->visibility);
        $this->assertEquals(ImportStatus::CREATED, $mapping->status);
        $this->assertEquals(['paths' => ['folder']], $mapping->filters);
    }
    
    public function test_system_visibility_invalid_during_import_map_creation(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $response = $this
            ->actingAs($user)
            ->from("imports/{$import->ulid}/mappings/create")
            ->post("/imports/{$import->ulid}/mappings/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'paths' => ['folder'],
                'visibility' => Visibility::SYSTEM->value,
            ]);

        $response->assertSessionHasErrors(['visibility' => 'The selected visibility is invalid.']);

        $response->assertRedirectToRoute('imports.mappings.create', $import);

        $mapping = $import->maps()->first();

        $this->assertNull($mapping);
    }
    
    public function test_public_visibility_invalid_during_import_map_creation(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $response = $this
            ->actingAs($user)
            ->from("imports/{$import->ulid}/mappings/create")
            ->post("/imports/{$import->ulid}/mappings/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'paths' => ['folder'],
                'visibility' => Visibility::PUBLIC->value,
            ]);

            $response->assertSessionHasErrors(['visibility' => 'The selected visibility is invalid.']);

        $response->assertRedirectToRoute('imports.mappings.create', $import);

        $mapping = $import->maps()->first();

        $this->assertNull($mapping);
    }
    
    public function test_custom_schedule_not_currently_accepted_during_import_map_creation(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $response = $this
            ->actingAs($user)
            ->from("imports/{$import->ulid}/mappings/create")
            ->post("/imports/{$import->ulid}/mappings/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'paths' => ['folder'],
                'schedule' => ImportSchedule::CUSTOM->value,
            ]);

        $response->assertSessionHasErrors(['schedule' => 'The selected schedule is invalid.']);

        $response->assertRedirectToRoute('imports.mappings.create', $import);

        $mapping = $import->maps()->first();

        $this->assertNull($mapping);
    }


    public function test_import_map_updated(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->has(ImportMap::factory(['status' => ImportStatus::COMPLETED, 'recursive' => true]), 'maps')
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $map = $import->maps()->first();

        $response = $this
            ->actingAs($user)
            ->from("/mappings/{$map->ulid}/edit")
            ->put("/mappings/{$map->ulid}/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'visibility' => Visibility::PROTECTED->value,
                'schedule' => ImportSchedule::DAILY->value,
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirectToRoute('mappings.show', $map);

        $mapping = $map->fresh();

        $this->assertInstanceOf(ImportMap::class, $mapping);

        $this->assertTrue($mapping->import->is($import));
        $this->assertTrue($mapping->mappedTeam->is($user->currentTeam));
        $this->assertTrue($mapping->mappedUploader->is($user));
        $this->assertEquals(Visibility::PROTECTED, $mapping->visibility);
        $this->assertInstanceOf(ImportScheduleSettings::class, $mapping->schedule);
        $this->assertTrue($mapping->schedule->is(ImportSchedule::DAILY));
    }

    public function test_custom_schedule_not_currently_accepted_during_import_map_update(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $import = Import::factory()
            ->recycle($user)
            ->has(ImportMap::factory(['status' => ImportStatus::COMPLETED, 'recursive' => true]), 'maps')
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $map = $import->maps()->first();

        $response = $this
            ->actingAs($user)
            ->from("/mappings/{$map->ulid}/edit")
            ->put("/mappings/{$map->ulid}/", [
                'recursive' => false,
                'team' => $user->currentTeam->getKey(),
                'visibility' => Visibility::PROTECTED->value,
                'schedule' => ImportSchedule::CUSTOM->value,
            ]);

        $response->assertSessionHasErrors(['schedule' => 'The selected schedule is invalid.']);

        $response->assertRedirectToRoute('mappings.edit', $map);
    }
    
}
