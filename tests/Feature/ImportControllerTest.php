<?php

namespace Tests\Feature;

use App\Models\Import;
use App\Models\ImportSource;
use App\Models\ImportStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_import_requires_authentication(): void
    {
        $response = $this->get('/imports');

        $response->assertRedirectToRoute('login');
    }

    public function test_listing_imports_requires_authorization(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/imports');

        $response->assertForbidden();
    }

    public function test_imports_can_be_listed(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->has(Import::factory())
            ->create();

        
        $response = $this
            ->actingAs($user)
            ->get('/imports');

        $response->assertStatus(200);

        $response->assertViewIs('import.index');

        $response->assertViewHas('imports', Import::all());
    }

    public function test_import_creation_page_loads(): void
    {
        $user = User::factory()
            ->admin()
            ->create();

        
        $response = $this
            ->actingAs($user)
            ->get('/imports/create');

        $response->assertStatus(200);

        $response->assertViewIs('import.create');

        $response->assertViewHas('sources', ImportSource::cases());
    }

    public function test_import_created(): void
    {
        $user = User::factory()
            ->admin()
            ->create();

        $this->withExceptionHandling();
        
        $response = $this
            ->actingAs($user)
            ->from('/imports/create')
            ->post('/imports', [
                'source' => 'webdav',
                'url' => 'https://service-url',
                'user' => 'a-user',
                'password' => 'a-password',
            ]);

        $response->assertSessionHasNoErrors();

        $import = Import::first();

        $this->assertNotNull($import);

        $this->assertEquals(ImportSource::WEBDAV, $import->source);
        $this->assertEquals(ImportStatus::CREATED, $import->status);
        $this->assertEquals([
            'url' => 'https://service-url',
            'user' => 'a-user',
            'password' => 'a-password',
        ], $import->configuration);
        
        $this->assertTrue($import->creator->is($user));

        $response->assertRedirect(route('imports.show', $import));
    }

    public function test_import_can_be_viewed(): void
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->has(Import::factory())
            ->create();

        $import = Import::first();
        
        $response = $this
            ->actingAs($user)
            ->get('/imports/' . $import->ulid);

        $response->assertStatus(200);

        $response->assertViewIs('import.show');

        $response->assertViewHas('import', $import);
    }
}
