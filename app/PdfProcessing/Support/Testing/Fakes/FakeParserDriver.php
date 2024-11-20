<?php

namespace App\PdfProcessing\Support\Testing\Fakes;

use App\PdfProcessing\Contracts\Driver;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\PdfProcessingManager;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use PHPUnit\Framework\Assert as PHPUnit;

class FakeParserDriver implements Driver
{

    protected $pdfRequests;


    public function __construct(
        protected Collection $fakedExtractions
    )
    {

        $this->pdfRequests = collect();
    }


    public function text(DocumentReference $document, array $options = []): DocumentContent
    {
        $this->pdfRequests->add($document);

        $content = $this->fakedExtractions->get($this->pdfRequests->count()-1, $this->fakedExtractions->first());

        if(blank($content)){
            throw new Exception("No mocked content response for document [{$document->path}]");
        }

        return $content;
    }

    public function properties(DocumentReference $document): DocumentProperties
    {
        return new DocumentProperties(
            title: "A fake document title",
            description: "A fake document description",
            author: null,
            pages: 1,
            pageSize: null,
            isTaggedPdf: false,
            createdAt: null,
            modifiedAt: null,
            producedWith: null,
        );
    }


    /**
     * Assert the total count of parse requests that were pushed.
     *
     * @param  int  $expectedCount
     * @return void
     */
    public function assertCount($expectedCount)
    {
        $actualCount = $this->pdfRequests->count();

        PHPUnit::assertSame(
            $expectedCount, $actualCount,
            "Expected {$expectedCount} parse requests to be pushed, but found {$actualCount} instead."
        );
    }
    
    /**
     * Assert the total count of parse requests that were pushed.
     *
     * @param  int  $expectedCount
     * @return void
     */
    public function assertNoParsingRequests()
    {
        $this->assertCount(0);
    }

}