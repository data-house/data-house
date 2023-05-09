<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use App\Models\Disk;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\Drivers\XpdfDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class XpdfDriverTest extends TestCase
{

    public function test_driver_return_file_info(): void
    {
        $driver = new XpdfDriver();

        $info = $driver->info(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

        $this->assertInstanceOf(DocumentProperties::class, $info);
        $this->assertEquals('Test document', $info->title);
        $this->assertEquals('', $info->description);
        $this->assertEquals('Data House Author', $info->author);
        $this->assertEquals(1, $info->pages);
        $this->assertEquals('612 x 792 pts (letter) (rotated 0 degrees)', $info->pageSize);
        $this->assertTrue($info->isTaggedPdf);
        $this->assertEquals('Microsoft® Word for Microsoft 365', $info->producedWith);
        $this->assertEquals('2023-05-09 11:34:41', $info->createdAt->toDateTimeString());
        $this->assertEquals('2023-05-09 11:34:41', $info->modifiedAt->toDateTimeString());
    }
}
