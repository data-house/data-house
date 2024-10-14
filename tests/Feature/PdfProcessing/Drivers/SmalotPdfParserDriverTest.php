<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use Tests\TestCase;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;

class SmalotPdfParserDriverTest extends TestCase
{

    public function test_driver_return_file_info(): void
    {
        $driver = new SmalotPdfParserDriver();

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

    public function test_driver_return_file_content(): void
    {
        $driver = new SmalotPdfParserDriver();

        $reference = DocumentReference::build('application/pdf')->path(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

        $documentContent = $driver->text($reference);

        $this->assertInstanceOf(DocumentContent::class, $documentContent);

        $text = $documentContent->all();

        $this->assertStringContainsString("This is the header", $text);
        $this->assertStringContainsString("This is a test PDF to be used as input in unit \ntests", $text);
        $this->assertStringContainsString("This is a heading 1", $text);
        $this->assertStringContainsString("This is a paragraph below heading 1", $text);

        $this->assertEquals([
            "category" => "doc",
            "attributes" => null,
            "content" => [[
              "category" => "page",
              "attributes" => [
                "page" => 1
              ],
              "content" => [
                [
                  "category" => "body",
                  "content" => "This is the header \n \n1 \n \nThis is a test PDF to be used as input in unit \ntests \n \nThis is a heading 1 \nThis is a paragraph below heading 1",
                  "marks" => [],
                  "attributes" => [],
                ]
              ]
            ]]
          ], $documentContent->asArray());

    }
}
