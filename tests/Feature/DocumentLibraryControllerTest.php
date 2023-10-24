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
    
    public function test_ask_questions_to_library_allowed(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $response = $this->actingAs($user)
            ->get('/library');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertSee('Ask a question to all documents in the library...');
    }
    
    public function test_ask_questions_to_library_not_allowed(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/library');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertDontSee('Ask a question to all documents in the library...');
    }
    
    public function test_library_shows_documents(): void
    {

        $documents = Document::factory()
            ->visibleByAnyUser()
            ->count(2)
            ->create();

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

        $documents = Document::factory()
            ->visibleByAnyUser()
            ->count(2)
            ->create();

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


    public function test_only_visible_documents_are_listed()
    {
        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $documentsVisibleByTeam = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->count(2)
            ->create();

        $documentsVisibleAllUsers = Document::factory()
            ->visibleByAnyUser()
            ->count(2)
            ->create();

        $notAccessibleDocuments = Document::factory()
            ->count(2)
            ->create();

        $visibleDocuments = $documentsVisibleByTeam->merge($documentsVisibleAllUsers);

        $response = $this->actingAs($user)
            ->get('/library');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertViewHas('documents');

        $actualDocuments = $response->viewData('documents');

        $this->assertEquals($visibleDocuments->pluck('id')->toArray(), $actualDocuments->pluck('id')->toArray());
    }
}
