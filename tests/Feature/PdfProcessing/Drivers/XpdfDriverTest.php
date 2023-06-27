<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use App\Models\Disk;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\Drivers\XpdfDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class XpdfDriverTest extends TestCase
{
    
    public function test_xpdf_driver_supported()
    {
        Process::preventStrayProcesses();

        Process::fake([
            'pdfinfo *' => "pdfinfo version 4.04 [www.xpdfreader.com]",
            'pdftotext *' => "pdfinfo version 4.04 [www.xpdfreader.com]",
        ]);

        $usable = XpdfDriver::hasDependenciesInstalled();

        Process::assertRan('pdfinfo -v');
        Process::assertRan('pdftotext -v');

        $this->assertTrue($usable);
    }

    public function test_xpdf_driver_not_supported()
    {
        Process::preventStrayProcesses();

        Process::fake([
            'pdfinfo *' => Process::result(exitCode: 1),
            'pdftotext *' => Process::result(exitCode: 1),
        ]);

        $usable = XpdfDriver::hasDependenciesInstalled();

        Process::assertRan('pdfinfo -v');
        Process::assertRan('pdftotext -v');

        $this->assertFalse($usable);
    }

    public function test_xpdf_driver_return_file_info(): void
    {
        if (! XpdfDriver::hasDependenciesInstalled()) {
            $this->markTestSkipped('Xpdf dependencies are not installed.');

            return;
        }

        $driver = new XpdfDriver();

        $reference = DocumentReference::build('application/pdf')->path(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

        $info = $driver->properties($reference);

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
    
    public function test_xpdf_info_executed(): void
    {
        Process::preventStrayProcesses();

        $pdfInfoResult = <<<'PDFINFO'
        Title:          Test document
        Author:         Data House Author
        Creator:        Microsoft® Word for Microsoft 365
        Producer:       Microsoft® Word for Microsoft 365
        CreationDate:   2023-05-09T11:34:41+02:00
        ModDate:        2023-05-09T11:34:41+02:00
        Tagged:         yes
        Form:           none
        Pages:          1
        Encrypted:      no
        Page size:      612 x 792 pts (letter) (rotated 0 degrees)
        File size:      70610 bytes
        Optimized:      no
        PDF version:    1.7
        PDFINFO;

        Process::fake([
            'pdfinfo *' => $pdfInfoResult,
        ]);

        $driver = new XpdfDriver();

        $reference = DocumentReference::build('application/pdf')->path(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

        $info = $driver->properties($reference);

        Process::assertRan('pdfinfo -meta -rawdates ' . base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

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

    public function test_driver_return_file_content(): void
    {
        if (! XpdfDriver::hasDependenciesInstalled()) {
            $this->markTestSkipped('Xpdf dependencies are not installed.');

            return;
        }

        $driver = new XpdfDriver();

        $reference = DocumentReference::build('application/pdf')->path(base_path('tests/fixtures/documents/data-house-test-doc.pdf'));

        $text = $driver->text($reference);

        $this->assertStringContainsString("This is the header", $text);
        $this->assertStringContainsString("This is a test PDF to be used as input in unit\r\ntests", $text);
        $this->assertStringContainsString("This is a heading 1", $text);
        $this->assertStringContainsString("This is a paragraph below heading", $text);

    }

}
