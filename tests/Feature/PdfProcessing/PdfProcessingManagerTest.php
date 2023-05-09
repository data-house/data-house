<?php

namespace Tests\Feature\PdfProcessing\Drivers;

use App\Models\Disk;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\Drivers\SmalotPdfParserDriver;
use App\PdfProcessing\Drivers\XpdfDriver;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfProcessingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfProcessingManagerTest extends TestCase
{

    public function test_default_driver_uses_smalot_library(): void
    {
        $driver = app()->make(PdfProcessingManager::class)->getDefaultDriver();

        $this->assertInstanceOf(SmalotPdfParserDriver::class, $driver);
    }
    
    public function test_smalot_driver_can_be_created(): void
    {
        $driver = Pdf::driver('smalot');

        $this->assertInstanceOf(SmalotPdfParserDriver::class, $driver);
    }
    
    public function test_xpdf_driver_can_be_created(): void
    {
        $driver = Pdf::driver('xpdf');

        $this->assertInstanceOf(XpdfDriver::class, $driver);
    }
}
