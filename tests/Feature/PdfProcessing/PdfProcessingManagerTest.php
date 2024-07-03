<?php

namespace Tests\Feature\PdfProcessing;

use App\Models\Disk;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\Drivers\CopilotPdfParserDriver;
use App\PdfProcessing\Drivers\ExtractorServicePdfParserDriver;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use App\PdfProcessing\Drivers\XpdfDriver;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfDriver;
use App\PdfProcessing\PdfProcessingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

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
            'pdf.default' => PdfDriver::EXTRACTOR_SERVICE->value,
        ]);

        $driver = app()->make(PdfProcessingManager::class)->getDefaultDriver();

        $this->assertEquals('extractor', $driver);
    }
    
    public function test_smalot_driver_can_be_created(): void
    {
        $driver = Pdf::driver('smalot');

        $this->assertInstanceOf(SmalotPdfParserDriver::class, $driver);
    }
    
    public function test_extractor_driver_can_be_created(): void
    {
        config(['pdf.processors.extractor' => [
            'host' => 'http://localhost:5000',
        ]]);

        $driver = Pdf::driver('extractor');

        $this->assertInstanceOf(ExtractorServicePdfParserDriver::class, $driver);
    }
}
