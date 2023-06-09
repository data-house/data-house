<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\MimeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PdfViewerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_viewer_url_can_be_generated(): void
    {
        $document = Document::factory()
            ->create();

        $url = $document->viewerUrl(page: 3);

        $file = urlencode("/documents/{$document->ulid}/download");

        $this->assertEquals(config('app.url')."/pdf-viewer?document={$document->ulid}&file={$file}&page=3#page=3", $url);
    }

    public function test_pdf_viewer_url_generated_when_conversion_available(): void
    {
        $document = Document::factory()
            ->create([
                'mime' => 'image/png',
                'conversion_disk_name' => 'local',
                'conversion_disk_path' => 'converted.pdf',
                'conversion_file_mime' => MimeType::APPLICATION_PDF->value,
            ]);

        $url = $document->viewerUrl(page: 3);

        $file = urlencode("/documents/{$document->ulid}/download");

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
    
    public function test_pdf_viewer_doesnt_load_if_document_is_not_pdf(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'mime' => 'image/png',
            ]);

        $response = $this->actingAs($user)
            ->get(route('pdf.viewer', [
                'document' => $document->ulid,
                'file' => Str::replace(config('app.url'),'', $document->url()),
                'page' => 1
            ]) . "#page=1");

        $response->assertUnsupportedMediaType();

    }
    
    public function test_pdf_viewer_load_if_pdf_conversion_available(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'mime' => 'image/png',
                'conversion_disk_name' => 'local',
                'conversion_disk_path' => 'converted.pdf',
                'conversion_file_mime' => MimeType::APPLICATION_PDF->value,
            ]);

        $response = $this->actingAs($user)
            ->get(route('pdf.viewer', [
                'document' => $document->ulid,
                'file' => Str::replace(config('app.url'),'', $document->url()),
                'page' => 1
            ]) . "#page=1");

        $response->assertStatus(200);

        $response->assertViewIs('pdf.viewer');

        $response->assertViewHas('document', $document);
        $response->assertViewHas('page', 1);
    }

    
    public function test_pdf_viewer_doesnt_load_if_conversion_not_a_pdf(): void
    {
        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'mime' => 'image/png',
                'conversion_disk_name' => 'local',
                'conversion_disk_path' => 'converted.png',
                'conversion_file_mime' => 'image/png',
            ]);

        $response = $this->actingAs($user)
            ->get(route('pdf.viewer', [
                'document' => $document->ulid,
                'file' => Str::replace(config('app.url'),'', $document->url()),
                'page' => 1
            ]) . "#page=1");

        $response->assertUnsupportedMediaType();

    }
    
}
