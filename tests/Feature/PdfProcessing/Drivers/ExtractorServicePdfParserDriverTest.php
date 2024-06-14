<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use App\Models\Disk;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\Drivers\ExtractorServicePdfParserDriver;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use App\PdfProcessing\Exceptions\PdfParsingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtractorServicePdfParserDriverTest extends TestCase
{

    public function test_driver_return_file_info(): void
    {
        $driver = new ExtractorServicePdfParserDriver(['host' => 'http://localhost:5000/']);

        $reference = DocumentReference::build('application/pdf')->path(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

        $info = $driver->properties($reference);

        $this->assertInstanceOf(DocumentProperties::class, $info);
        $this->assertEquals('Test document', $info->title);
        $this->assertEquals('', $info->description);
        $this->assertEquals('Data House Author', $info->author);
        $this->assertEquals(1, $info->pages);
        $this->assertNull($info->pageSize);
        $this->assertNull($info->isTaggedPdf);
        $this->assertEquals('Microsoft® Word for Microsoft 365', $info->producedWith);
        $this->assertEquals('2023-05-09 11:34:41', $info->createdAt->toDateTimeString());
        $this->assertEquals('2023-05-09 11:34:41', $info->modifiedAt->toDateTimeString());
    }

    public function test_extractor_driver_return_file_content(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5002/extract-text' => Http::response([
                "fonts" => [[
                    "name" => "fira sans",
                    "id" => "font-300",
                    "is-bold" => false,
                    "is-type3" => false,
                    "is-italic" => false,
                    ],
                ],
                "text" => [
                    [
                        "text" =>"This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1",
                        "metadata" => [
                            "role" => "header",
                            "color" => [
                                "r" => 78,
                                "b" => 189,
                                "g" => 128,
                                "id" => "color-39",
                            ],
                            "positions" => [
                                0 => [
                                    "minY" => 565.0,
                                    "minX" => 62.1,
                                    "maxY" => 577.6,
                                    "maxX" => 427.2,
                                ],
                                ],
                            "font" => [
                                "name" => "fira sans",
                                "id" => "font-300",
                                "is-bold" => false,
                                "is-type3" => false,
                                "is-italic" => false,
                            ],
                            "page" => 1
                        ],
                    ],
                ],      
                "colors" =>  [
                    [
                        "r" => 247,
                        "b" => 70,
                        "g" => 150,
                        "id" => "color-40",
                    ],
                ],
            ], 200),
        ]);

        $driver = new ExtractorServicePdfParserDriver(['host' => 'http://localhost:5002/', 'driver' => 'pdfact']);

        $reference = DocumentReference::build('application/pdf')->url('http://document-url');
        
        $content = $driver->text($reference)->pages();
        $this->assertEquals("This is the header 1 This is a test PDF to be used as input in unit tests This is a heading 1 This is a paragraph below heading 1", $content[1]);
        $this->assertEquals([1], array_keys($content));

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost:5002/extract-text' &&
                   $request['mime_type'] == 'application/pdf' &&
                   $request['url'] == 'http://document-url' &&
                   $request['driver'] == 'pdfact';
        });

    }

    public function test_driver_handle_errors(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'http://localhost:5000/extract-text' => Http::response([
                "detail" => "Error while downloading file [404 Client Error: Not Found for url:"
            ], 500),
        ]);

        $driver = new ExtractorServicePdfParserDriver(['host' => 'http://localhost:5000/']);

        $reference = DocumentReference::build('application/pdf')->url('http://non-existing-doc-url');

        $this->expectException(PdfParsingException::class);
        $this->expectExceptionMessage('Unable to process the file');

        $text = $driver->text($reference);

    }
}
