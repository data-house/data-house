<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PdfViewerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_viewer_url_can_be_generated(): void
    {
        $document = Document::factory()
            ->create();

        $url = $document->viewerUrl(page: 3);

        $file = urlencode("documents/{$document->ulid}/download");

        $this->assertEquals(config('app.url')."/pdf-viewer?document={$document->ulid}&file={$file}&page=3#page=3", $url);
    }

    public function test_pdf_viewer_requires_authentication(): void
    {
        $document = Document::factory()
            ->create();

        $response = $this->get($document->viewerUrl());

        $response->assertRedirectToRoute('login');
    }
    
    public function test_pdf_viewer_loads(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create();

        $response = $this->actingAs($user)
            ->get($document->viewerUrl());

        $response->assertStatus(200);

        $response->assertViewIs('pdf.viewer');

        $response->assertViewHas('document', $document);
        $response->assertViewHas('page', 1);
    }
    
}
