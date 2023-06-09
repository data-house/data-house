<?php

namespace Tests\Feature;

use App\Models\Disk;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentDownloadControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_document_download_requires_authentication(): void
    {
        $document = Document::factory()
            ->create();

        $response = $this->get("/documents/{$document->ulid}/download");

        $response->assertRedirectToRoute('login');
    }
    
    public function test_document_can_be_downloaded(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $response = $this->actingAs($user)
            ->get("/documents/{$document->ulid}/download");

        $response->assertStatus(200);

        $response->assertDownload();
    }
    
    public function test_document_can_be_downloaded_with_inline_disposition(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $response = $this->actingAs($user)
            ->get("/documents/{$document->ulid}/download?disposition=inline");

        $response->assertStatus(200);

        $response->assertHeader('content-disposition', 'inline; filename=test.pdf');
    }

    
    public function test_invalid_disposition_parameter_causes_download(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $user = User::factory()->withPersonalTeam()->manager()->create();

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $response = $this->actingAs($user)
            ->get("/documents/{$document->ulid}/download?disposition=preview");

        $response->assertStatus(200);

        $response->assertDownload();
    }
}
