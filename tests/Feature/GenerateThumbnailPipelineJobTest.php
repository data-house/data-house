<?php

namespace Tests\Feature;

use App\DocumentThumbnail\Facades\Thumbnail;
use App\DocumentThumbnail\FileThumbnail;
use App\Jobs\Pipeline\Document\GenerateThumbnail;
use App\Models\Disk;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateThumbnailPipelineJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_thumbnail_generated(): void
    {
        config([
            'thumbnail.enable' => true,
            'thumbnail.drivers.imaginary' => ['url' => 'http://imaginary'],
        ]);

        Storage::fake('local');
        
        Storage::fake('thumbnails');

        Thumbnail::shouldReceive('driver')->andReturn('imaginary');
        
        Thumbnail::shouldReceive('thumbnail')->andReturn(new FileThumbnail('thumbnails', 'test.jpg', 'image/jpeg'));

        Http::preventStrayRequests();

        $model = Document::factory()
            ->hasPipelineRuns(1)
            ->create();

        $job = new GenerateThumbnail($model, $model->latestPipelineRun);

        $job->handle();

        $document = $model->fresh();

        $this->assertEquals(Disk::THUMBNAILS->value, $document->thumbnail_disk_name);
        $this->assertEquals('test.jpg', $document->thumbnail_disk_path);
    }
    
    
}
