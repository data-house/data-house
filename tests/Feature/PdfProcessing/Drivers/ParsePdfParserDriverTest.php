<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use App\PdfProcessing\DocumentContent;
use Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\Drivers\ParsePdfParserDriver;
use App\PdfProcessing\Exceptions\PdfParsingException;
use OneOffTech\Parse\Client\Requests\ExtractTextRequest;
use Saloon\Config as SaloonConfig;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class ParsePdfParserDriverTest extends TestCase
{

    public function test_driver_return_file_info(): void
    {
        $driver = new ParsePdfParserDriver(['host' => 'http://localhost:5000/']);

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

    public function test_parse_return_file_content(): void
    {
        SaloonConfig::preventStrayRequests();
        MockClient::destroyGlobal();
        
        $mockClient = MockClient::global([
            ExtractTextRequest::class => MockResponse::fixture('extract-text'),
        ]);

        $driver = new ParsePdfParserDriver(['host' => 'http://localhost:5000/', 'processor' => 'pdfact']);

        $reference = DocumentReference::build('application/pdf')->url('http://document-url');

        $output = $driver->text($reference);

        $this->assertInstanceOf(DocumentContent::class, $output);

        $this->assertStringContainsString("This is the title of the document", $output->all());
        
        $pages = $output->pages();
        
        $this->assertEquals('1 First chapter'.PHP_EOL.'This is an example text.', $pages[1]->text());

        $expectedStructuredContent = json_decode(file_get_contents('./tests/Fixtures/Saloon/extract-text.json'), true);

        $this->assertEquals(json_decode($expectedStructuredContent['data'], true), $output->asStructured());

    }

    public function test_parse_handle_errors(): void
    {
        SaloonConfig::preventStrayRequests();
        MockClient::destroyGlobal();
        
        $mockClient = MockClient::global([
            ExtractTextRequest::class => MockResponse::fixture('extract-text-non-existing'),
        ]);

        $driver = new ParsePdfParserDriver(['host' => 'http://localhost:5000/']);

        $reference = DocumentReference::build('application/pdf')->url('http://non-existing-doc-url');

        $this->expectException(PdfParsingException::class);
        $this->expectExceptionMessage('Unable to process the file');

        $text = $driver->text($reference);

    }
}
