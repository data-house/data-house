<?php

namespace Tests\Feature;

use App\Livewire\DocumentVisibilitySelector;
use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use App\Models\ImportDocument;
use App\Models\Visibility;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_creation_form_requires_login(): void
    {
        $response = $this->get('/documents/create');

        $response->assertRedirectToRoute('login');
    }
    
    public function test_creation_form_loads(): void
    {
        $user = User::factory()->withPersonalTeam()->guest()->create();

        $response = $this->actingAs($user)
            ->get('/documents/create');

        $response->assertOk();

        $response->assertViewIs('document.create');
        
        $response->assertSee('Upload');
    }
    
    public function test_document_can_be_uploaded(): void
    {
        Storage::fake('documents');

        $user = User::factory()->withPersonalTeam()->guest()->create();

        $response = $this->actingAs($user)
            ->post('/documents', [
                'document' => UploadedFile::fake()->image('photo1.jpg', 200, 200),
            ]);

        $response->assertRedirectToRoute('documents.library');

        $response->assertSessionHas('flash.banner', 'Document uploaded.');
        
        $document = Document::first();

        $this->assertEquals('documents', $document->disk_name);
        $this->assertNotEmpty($document->disk_path);
        $this->assertEquals('photo1.jpg', $document->title);
        $this->assertEquals('image/jpeg', $document->mime);
        $this->assertTrue($document->uploader->is($user));
        $this->assertTrue($document->team->is($user->currentTeam));
        $this->assertEquals(Visibility::TEAM, $document->visibility);

        $this->assertStringNotContainsString('/', $document->disk_path);

        Storage::disk('documents')->assertExists($document->disk_path);
    }

    public function test_document_details_page_not_loadable_if_user_doesnt_have_view_permission()
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->create([
                'title' => 'The title of the document'
            ]);

        $response = $this->actingAs($user)
            ->get('/documents/' . $document->ulid);

        $response->assertForbidden();
    }

    public function test_document_details_page_loads()
    {        
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create([
                'title' => 'The title of the document'
            ]);

        $response = $this->actingAs($user)
            ->get('/documents/' . $document->ulid);

        $response->assertOk();

        $response->assertViewIs('document.show');
        
        $response->assertSee('Open');
        $response->assertSee('Edit');
        $response->assertSee('The title of the document');
        $response->assertSee(Visibility::TEAM->label());
        $response->assertSee($user->name);
        $response->assertSeeLivewire(DocumentVisibilitySelector::class);
    }
    
    public function test_document_details_page_shows_import_source()
    {        
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create([
                'title' => 'The title of the document'
            ]);

        $importDoc = ImportDocument::factory()
            ->create([
                'uploaded_by' => $user->getKey(),
                'team_id' => $user->currentTeam->getKey(),
                'document_id' => $document->getKey(),
            ]);

        $response = $this->actingAs($user)
            ->get('/documents/' . $document->ulid);

        $response->assertOk();

        $response->assertViewIs('document.show');
        
        $response->assertSee('Open');
        $response->assertSee('Edit');
        $response->assertSee('The title of the document');
        $response->assertSee(Visibility::TEAM->label());
        $response->assertSee($user->name);
        $response->assertSee('Imported from');
        $response->assertSee($importDoc->source_path);
        $response->assertSee($importDoc->import->source->name);
        $response->assertSeeLivewire(DocumentVisibilitySelector::class);
    }
    
    public function test_document_details_page_shows_import_source_only_if_browsed_by_owning_team()
    {        
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByAnyUser()
            ->create([
                'title' => 'The title of the document'
            ]);

        $importDoc = ImportDocument::factory()
            ->create([
                'document_id' => $document->getKey(),
            ]);

        $response = $this->actingAs($user)
            ->get('/documents/' . $document->ulid);

        $response->assertOk();

        $response->assertViewIs('document.show');
        
        $response->assertSee('Open');
        $response->assertSee('Edit');
        $response->assertSee('The title of the document');
        $response->assertSee(Visibility::TEAM->label());
        $response->assertSee($user->name);
        $response->assertDontSee('Imported from');
        $response->assertDontSee($importDoc->source_path);
        $response->assertDontSee($importDoc->import->source->name);
        $response->assertSeeLivewire(DocumentVisibilitySelector::class);
    }

    public function test_document_editing_page_loads()
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create([
                'title' => 'The title of the document'
            ]);

        $response = $this->actingAs($user)
            ->get("/documents/{$document->ulid}/edit");

        $response->assertOk();

        $response->assertViewIs('document.edit');
        
        $response->assertSee('Save');
        $response->assertSee('The title of the document');
        $response->assertSee('description');
    }

    public function test_document_can_be_updated()
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create([
                'title' => 'The title of the document'
            ]);

        $response = $this->actingAs($user)
            ->from("/documents/{$document->ulid}/edit")
            ->put("/documents/{$document->ulid}", [
                'title' => 'New title',
                'description' => 'New abstract',
            ]);

        $response->assertRedirect(route('documents.show', $document));

        $response->assertSessionHas('flash.banner', 'New title updated.');
        
        $updatedDocument = Document::first();
        
        $this->assertEquals('New title', $updatedDocument->title);
        $this->assertEquals('New abstract', $updatedDocument->description);
    }

    public static function generateInvalidTitles()
    {
        return [
            [Str::random(251)],
            [null],
        ];
    }
    
    /**
     * @dataProvider generateInvalidTitles
     */
    public function test_document_update_validates_title($title)
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create([
                'title' => 'The title of the document'
            ]);

        $response = $this->actingAs($user)
            ->from("/documents/{$document->ulid}/edit")
            ->put("/documents/{$document->ulid}", [
                'title' => $title,
                'description' => 'New abstract',
            ]);

        $response->assertRedirect(route('documents.edit', $document));

        $response->assertSessionHasErrors('title');
        
        $updatedDocument = Document::first();
        
        $this->assertEquals('The title of the document', $updatedDocument->title);
    }


    public static function generateInvalidDescriptions()
    {
        return [
            [Str::random(2001)],
        ];
    }
    
    /**
     * @dataProvider generateInvalidDescriptions
     */
    public function test_document_update_validates_description($description)
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create([
                'title' => 'The title of the document',
                'description' => 'The original abstract',
            ]);

        $response = $this->actingAs($user)
            ->from("/documents/{$document->ulid}/edit")
            ->put("/documents/{$document->ulid}", [
                'title' => 'The title of the document',
                'description' => $description,
            ]);

        $response->assertRedirect(route('documents.edit', $document));

        $response->assertSessionHasErrors('description');
        
        $updatedDocument = Document::first();
        
        $this->assertEquals('The original abstract', $updatedDocument->description);
    }
}
