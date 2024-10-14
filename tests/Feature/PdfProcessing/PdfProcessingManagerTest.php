<?php

namespace Tests\Feature\PdfProcessing;

use Tests\TestCase;
use App\PdfProcessing\PdfDriver;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfProcessingManager;
use App\PdfProcessing\Drivers\ParsePdfParserDriver;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;

class PdfProcessingManagerTest extends TestCase
{

    public function test_default_driver_falls_back_to_smalot_library(): void
    {
        $driver = app()->make(PdfProcessingManager::class)->getDefaultDriver();

        $this->assertEquals('smalot', $driver);
    }
    
    public function test_default_driver_respect_configuration(): void
    {
        config([
            'pdf.default' => PdfDriver::PARSE->value,
        ]);

        $driver = app()->make(PdfProcessingManager::class)->getDefaultDriver();

        $this->assertEquals('parse', $driver);
    }
    
    public function test_smalot_driver_can_be_created(): void
    {
        $driver = Pdf::driver(PdfDriver::SMALOT);

        $this->assertInstanceOf(SmalotPdfParserDriver::class, $driver);
    }
    
    public function test_parse_driver_can_be_created(): void
    {
        config(['pdf.processors.parse' => [
            'host' => 'http://localhost:5000',
        ]]);

        $driver = Pdf::driver(PdfDriver::PARSE);

        $this->assertInstanceOf(ParsePdfParserDriver::class, $driver);
    }
}
