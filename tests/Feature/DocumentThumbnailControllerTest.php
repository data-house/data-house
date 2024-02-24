<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentThumbnailControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_document_thumbnail_requires_authentication(): void
    {
        $document = Document::factory()
            ->create();

        $response = $this->get("/documents/{$document->ulid}/thumbnail");

        $response->assertRedirectToRoute('login');
    }
    
    public function test_document_thumbnail(): void
    {
        Storage::fake('local');

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.jpg');

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create([
                'thumbnail_disk_name' => 'local',
                'thumbnail_disk_path' => 'test.jpg',
            ]);

        $response = $this->actingAs($user)
            ->get("/documents/{$document->ulid}/thumbnail");

        $response->assertStatus(200);

        $response->assertDownload();
    }
    
    public function test_fallback_returned_when_no_thumbnail_available(): void
    {
        Storage::fake('local');

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->visibleByUploader($user)
            ->create();

        $response = $this->actingAs($user)
            ->get("/documents/{$document->ulid}/thumbnail");

        $response->assertStatus(404);
    }
    
    
    
    
    
    
    
    
}
