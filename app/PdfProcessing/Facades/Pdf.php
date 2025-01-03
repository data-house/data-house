<?php

namespace App\PdfProcessing\Facades;

use App\PdfProcessing\PdfProcessingManager;
use App\PdfProcessing\Support\Testing\Fakes\PdfManagerFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\PdfProcessing\Contracts\Driver driver(string $driver = null)
 * @method static \App\PdfProcessing\DocumentContent text(\App\PdfProcessing\DocumentReference $document)
 * @method static \App\PdfProcessing\DocumentProperties properties(\App\PdfProcessing\DocumentReference $document)
 *
 * @see \App\PdfProcessing\PdfProcessingManager
 */
class Pdf extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param  array  $extractions the document content to return
     * @return \App\PdfProcessing\Support\Testing\Fakes\FakeParserDriver
     */
    public static function fake($driver = null, $extractions = [])
    {
        $actualPdfManager = static::isFake()
                ? static::getFacadeRoot()->pdfManager
                : static::getFacadeRoot();

        $driver = $driver ?: static::$app['config']->get('pdf.default');

        $fakeManager = tap(new PdfManagerFake(static::getFacadeApplication(), $extractions, $actualPdfManager), function ($fake): void {
            static::swap($fake);
        });

        return $fakeManager->driver($driver);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PdfProcessingManager::class;
    }
}