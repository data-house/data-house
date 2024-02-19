<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Flag;
use App\Models\Preference;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Pennant\Feature;
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
        Feature::define(Flag::editDocumentVisibility(), true);

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
            'Format',
            'Project',
            'Access',
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


    public function test_facets_and_filters_status_returned()
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
        $response->assertViewHas('searchQuery', null);
        $response->assertViewHas('filters', ['source' => 'all-teams']);
        $response->assertViewHas('is_search', false);
        $response->assertViewHas('facets', [
            'source' => ['all-teams', 'current-team'],
            'format' => [
                'PDF',
                'Word',
                'Spreadsheet',
                'Slideshow',
                'Image',
                'Compressed folder',
            ],
            'type' => DocumentType::cases(),
            'countries' => collect(),
            'regions' => collect(),
            'organizations' => [],
            'topic' => Topic::facets(),
        ]);
        $response->assertViewHas('applied_filters_count', 0);

        $response->assertSee('2 documents in the library');
    }

    public function test_source_filter_can_be_selected()
    {
        $documents = Document::factory()
            ->visibleByAnyUser()
            ->count(2)
            ->create();

        $user = User::factory()->withPersonalTeam()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/library?source=current-team');

        $response->assertOk();

        $response->assertViewIs('library.index');

        $response->assertViewHas('documents');
        $response->assertViewHas('searchQuery', null);
        $response->assertViewHas('filters', ['source' => 'current-team']);
        $response->assertViewHas('is_search', true);
        $response->assertViewHas('facets', [
            'source' => ['all-teams', 'current-team'],
            'format' => [
                'PDF',
                'Word',
                'Spreadsheet',
                'Slideshow',
                'Image',
                'Compressed folder',
            ],
            'type' => DocumentType::cases(),
            'countries' => collect(),
            'regions' => collect(),
            'organizations' => [],
            'topic' => Topic::facets(),
        ]);
        $response->assertViewHas('applied_filters_count', 1);

        $response->assertSee('1 Filter');
        $response->assertSee('0 documents found'); // documents are created using different teams
    }
}
