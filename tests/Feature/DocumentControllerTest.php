<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

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

        $this->assertStringNotContainsString('/', $document->disk_path);

        Storage::disk('documents')->assertExists($document->disk_path);
    }
}
