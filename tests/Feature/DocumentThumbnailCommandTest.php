<?php

namespace Tests\Feature;

use App\DocumentThumbnail\Facades\Thumbnail;
use App\DocumentThumbnail\FileThumbnail;
use App\Models\Disk;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentThumbnailCommandTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_thumbnail_generated(): void
    {
        config([
            'thumbnail.enable' => true,
            'thumbnail.drivers.imaginary' => ['url' => 'http://imaginary'],
        ]);

        Storage::fake('local');
        
        Storage::fake('thumbnails');

        Thumbnail::shouldReceive('driver')->andReturn('imaginary');
        
        Thumbnail::shouldReceive('thumbnail')->andReturn(new FileThumbnail('thumbnails', 'test.jpg', 'image/jpeg'));

        $document = Document::factory()
            ->create();

        Http::preventStrayRequests();

        $this->artisan('document:thumbnail', [
                'documents' => [$document->ulid],
            ])
            ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $this->assertEquals(Disk::THUMBNAILS->value, $updatedDocument->thumbnail_disk_name);
        $this->assertEquals('test.jpg', $updatedDocument->thumbnail_disk_path);
    }
    
    public function test_thumbnail_not_overwritten_if_present(): void
    {
        config([
            'thumbnail.enable' => true,
            'thumbnail.drivers.imaginary' => ['url' => 'http://imaginary'],
        ]);

        Storage::fake('local');
        
        Storage::fake('thumbnails');

        Thumbnail::shouldReceive();

        $document = Document::factory()
            ->create([
                'thumbnail_disk_name' => 'thumbnails',
                'thumbnail_disk_path' => 'thumb.jpg',
            ]);

        Http::preventStrayRequests();

        $this->artisan('document:thumbnail', [
                'documents' => [$document->ulid],
            ])
            ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $this->assertEquals(Disk::THUMBNAILS->value, $updatedDocument->thumbnail_disk_name);
        $this->assertEquals('thumb.jpg', $updatedDocument->thumbnail_disk_path);

        
    }
    
}
