<?php

namespace Tests\Feature\DocumentThumbnail\Drivers;

use App\DocumentThumbnail\Drivers\ImaginaryDriver;
use App\DocumentThumbnail\Exceptions\ConversionException;
use App\DocumentThumbnail\Exceptions\UnsupportedConversionException;
use App\DocumentThumbnail\FileThumbnail;
use App\Models\Document;
use App\PdfProcessing\DocumentReference;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImaginaryDriverTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_thumbnail_can_be_generated(): void
    {
        Storage::fake('local');

        Storage::fake('thumbnails');

        config(['app.internal_url' => 'http://app.internal.url/']);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/pipeline*' => Http::response('fakeJpeg', 200, ['Content-Type' => 'image/jpeg', 'Content-Length' => 8]),            
        ]);
        
        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'disk_name' => 'local',
            'disk_path' => 'test.pdf',
        ]);

        $driver = new ImaginaryDriver([
            'disk' => 'thumbnails',
            'url' => 'http://localhost:9000',
        ]);

        /**
         * @var \App\DocumentThumbnail\FileThumbnail
         */
        $thumbnailFile = $driver->thumbnail($document->asReference());

        $this->assertNotNull($thumbnailFile);
        $this->assertInstanceOf(FileThumbnail::class, $thumbnailFile);

        $this->assertFileExists($thumbnailFile->absolutePath());

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) use ($document) {

            $data = $request->data();

            return str($request->url())->startsWith('http://localhost:9000/pipeline') &&
                   str($data['operations'])->isJson() &&
                   str($data['operations'])->contains('convert') &&
                   str($data['url'])->startsWith("http://app.internal.url/documents/{$document->ulid}/internal-download");
        });

    }
    
    public function test_image_thumbnail_can_be_generated(): void
    {
        Storage::fake('local');

        Storage::fake('thumbnails');

        config(['app.internal_url' => 'http://app.internal.url/']);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/pipeline*' => Http::response('fakeJpeg', 200, ['Content-Type' => 'image/jpeg', 'Content-Length' => 8]),            
        ]);
        
        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $driver = new ImaginaryDriver([
            'disk' => 'thumbnails',
            'url' => 'http://localhost:9000',
        ]);

        /**
         * @var \App\DocumentThumbnail\FileThumbnail
         */
        $thumbnailFile = $driver->thumbnail(DocumentReference::build('image/jpeg')->path('path')->url('http://app.internal.url/documents/fake'));

        $this->assertNotNull($thumbnailFile);
        $this->assertInstanceOf(FileThumbnail::class, $thumbnailFile);

        $this->assertFileExists($thumbnailFile->absolutePath());

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) {

            $data = $request->data();

            return str($request->url())->startsWith('http://localhost:9000/pipeline') &&
                   str($data['operations'])->isJson() &&
                   !str($data['operations'])->contains('convert') &&
                   str($data['url'])->startsWith("http://app.internal.url/documents/fake");
        });

    }

    public function test_office_document_not_supported(): void
    {
        Storage::fake('local');

        Storage::fake('thumbnails');

        config(['app.internal_url' => 'http://app.internal.url/']);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/pipeline*' => Http::response('fakeJpeg', 200, ['Content-Type' => 'image/jpeg', 'Content-Length' => 8]),            
        ]);
        
        $driver = new ImaginaryDriver([
            'disk' => 'thumbnails',
            'url' => 'http://localhost:9000',
        ]);

        $this->expectException(UnsupportedConversionException::class);

        /**
         * @var \App\DocumentThumbnail\FileThumbnail
         */
        $thumbnailFile = $driver->thumbnail(DocumentReference::build(MimeType::fromExtension('docx'))->path('path')->url('http://app.internal.url/documents/fake'));

        Http::assertNothingSent();
    }
    
    public function test_thumbnail_not_generated_when_file_download_error(): void
    {
        Storage::fake('local');

        Storage::fake('thumbnails');

        config(['app.internal_url' => 'http://app.internal.url/']);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/pipeline*' => Http::response('{"message":"error fetching remote http image", "status": 403}', 403, ['Content-Type' => 'application/json']),
        ]);
        
        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'disk_name' => 'local',
            'disk_path' => 'test.pdf',
        ]);

        $driver = new ImaginaryDriver([
            'disk' => 'thumbnails',
            'url' => 'http://localhost:9000',
        ]);

        $this->expectException(ConversionException::class);

        /**
         * @var \App\DocumentThumbnail\FileThumbnail
         */
        $thumbnailFile = $driver->thumbnail($document->asReference());

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) use ($document) {

            $data = $request->data();

            return str($request->url())->startsWith('http://localhost:9000/pipeline') &&
                   str($data['operations'])->isJson() &&
                   str($data['operations'])->contains('convert') &&
                   str($data['url'])->startsWith("http://app.internal.url/documents/{$document->ulid}/internal-download");
        });

    }
    
    public function test_handling_of_partial_thumbnail_response(): void
    {
        Storage::fake('local');

        Storage::fake('thumbnails');

        config(['app.internal_url' => 'http://app.internal.url/']);

        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:9000/pipeline*' => Http::response('fakeJpeg', 200, ['Content-Type' => 'image/jpeg', 'Content-Length' => 10]),
        ]);
        
        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $document = Document::factory()->create([
            'disk_name' => 'local',
            'disk_path' => 'test.pdf',
        ]);

        $driver = new ImaginaryDriver([
            'disk' => 'thumbnails',
            'url' => 'http://localhost:9000',
        ]);

        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Not equal file size after download');

        /**
         * @var \App\DocumentThumbnail\FileThumbnail
         */
        $thumbnailFile = $driver->thumbnail($document->asReference());

        Http::assertSentCount(1);

        Http::assertSent(function (Request $request) use ($document) {

            $data = $request->data();

            return str($request->url())->startsWith('http://localhost:9000/pipeline') &&
                   str($data['operations'])->isJson() &&
                   str($data['operations'])->contains('convert') &&
                   str($data['url'])->startsWith("http://app.internal.url/documents/{$document->ulid}/internal-download");
        });

    }
}
