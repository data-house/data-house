<?php

namespace Tests\Feature;

use App\Models\Disk;
use App\Models\Document;
use App\Models\MimeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternalDocumentDownloadControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_document_download_requires_signed_route(): void
    {
        $document = Document::factory()
            ->create();

        $response = $this->get("/documents/{$document->ulid}/internal-download");

        $response->assertForbidden();
    }
    
    public function test_document_can_be_downloaded(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $user = User::factory()->withPersonalTeam()->manager()->create();

        config([
            'app.url' => 'http://localhost/',
            'app.internal_url' => 'http://localhost/',
        ]);

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $response = $this->actingAs($user)
            ->get($document->internalUrl());

        $response->assertStatus(200);

        $response->assertDownload('test.pdf');
    }
    
    public function test_converted_document_can_be_downloaded(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'converted.pdf');
        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.docx')), 'test.docx');

        $user = User::factory()->withPersonalTeam()->manager()->create();

        config([
            'app.url' => 'http://localhost/',
            'app.internal_url' => 'http://localhost/',
        ]);

        $document = Document::factory()
            ->for($user, 'uploader')
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.docx',
                'mime' => 'docx',
                'conversion_disk_path' => 'converted.pdf',
                'conversion_disk_name' => 'local',
                'conversion_file_mime' => 'application/pdf',
            ]);

        $ref = $document->asReference();

        $response = $this->actingAs($user)
            ->get($ref->url);

        $this->assertEquals('application/pdf', $ref->mimeType);

        $response->assertStatus(200);

        $response->assertDownload('converted.pdf');

    }
    
}
