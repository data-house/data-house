<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Preference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DocumentLibraryControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_library_access_requires_login(): void
    {
        $response = $this->get('/library');

        $response->assertRedirectToRoute('login');
    }
    
    public function test_library_page_loads(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/library');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertSee('Search');
        
        $response->assertSee('Upload Document');
    }
    
    public function test_library_shows_documents(): void
    {

        $documents = Document::factory()->count(2)->create();

        $user = User::factory()->withPersonalTeam()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/library');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertViewHas('documents');
        $actualDocuments = $response->viewData('documents');

        $this->assertEquals($documents->pluck('id')->toArray(), $actualDocuments->pluck('id')->toArray());
    }
    
    public function test_library_shows_documents_when_user_prefer_list_view(): void
    {

        $documents = Document::factory()->count(2)->create();

        $user = User::factory()
            ->withPersonalTeam()
            ->withPreference(Preference::VISUALIZATION_LAYOUT, 'list')
            ->guest()
            ->create();

        $response = $this->actingAs($user)
            ->get('/library');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertViewHas('documents');

        $response->assertSeeTextInOrder([
            'Document',
            'Type',
            'Countries',
            'Project',
        ]);

        $actualDocuments = $response->viewData('documents');

        $this->assertEquals($documents->pluck('id')->toArray(), $actualDocuments->pluck('id')->toArray());
    }
}
