<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use App\Models\Disk;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmalotPdfParserDriverTest extends TestCase
{

    public function test_driver_return_file_info(): void
    {
        $driver = new SmalotPdfParserDriver();

        $info = $driver->properties(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

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

        $text = $driver->text(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

        $this->assertStringContainsString("This is the header", $text);
        $this->assertStringContainsString("This is a test PDF to be used\t as input \tin unit \t\ntests", $text);
        $this->assertStringContainsString("This is a heading 1", $text);
        $this->assertStringContainsString("This is a pa\tragraph belo\tw heading 1", $text);

    }
}
