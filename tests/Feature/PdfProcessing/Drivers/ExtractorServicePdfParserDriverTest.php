<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use App\Models\Disk;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\Drivers\ExtractorServicePdfParserDriver;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use App\PdfProcessing\Exceptions\PdfParsingException;
use App\PdfProcessing\StructuredDocumentContent;
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

        $extractedContent = [

            "type" => "doc",
            "content" => [
                [
                    "category" => "page",
                    "attributes" => [
                        "page" => 1
                    ],
                    "content" => [
                        [
                            "role" => "heading",
                            "text" => "Heading Text",
                            "marks" => [
                                [
                                    "category" => "textStyle",
                                    "color" => [
                                        "r" => 78,
                                        "b" => 189,
                                        "g" => 128,
                                        "id" => "color-1"
                                    ],
                                    "font" => [
                                        "name" => "fira sans",
                                        "id" => "font-300",
                                        "size" => 18
                                    ]
                                ]
                            ],
                            "attributes" => [
                                "bounding_box" => [
                                    [
                                        "min_x" => 62.1,
                                        "min_y" => 493.0,
                                        "max_x" => 427.2,
                                        "max_y" => 505.6,
                                        "page" => 1
                                    ]
                                ]
                            ]
                        ],
                        [
                            "role" => "body",
                            "text" => "A paragraph on the first page",
                            "marks" => [
                                [
                                    "category" => "textStyle",
                                    "color" => [
                                        "r" => 0,
                                        "b" => 0,
                                        "g" => 0,
                                        "id" => "color-0"
                                    ],
                                    "font" => [
                                        "name" => "fira sans",
                                        "id" => "font-300",
                                        "size" => 16
                                    ]
                                ]
                            ],
                            "attributes" => [
                                "bounding_box" => [
                                    [
                                        "min_x" => 62.1,
                                        "min_y" => 460.1,
                                        "max_x" => 118.5,
                                        "max_y" => 482.7,
                                        "page" => 1
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];

        Http::fake([
            'http://localhost:5002/extract-text' => Http::response($extractedContent, 200),
        ]);

        $driver = new ExtractorServicePdfParserDriver(['host' => 'http://localhost:5002/', 'driver' => 'pdfact']);

        $reference = DocumentReference::build('application/pdf')->url('http://document-url');

        $output = $driver->text($reference);

        $this->assertInstanceOf(StructuredDocumentContent::class, $output);

        $this->assertEquals("Heading Text A paragraph on the first page", $output->all());
        
        $pages = $output->pages();
        
        $this->assertEquals("Heading Text A paragraph on the first page", $pages[1]);
        $this->assertEquals([1], array_keys($pages));

        $this->assertEquals($extractedContent, $output->asStructured());

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
