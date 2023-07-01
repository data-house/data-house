<?php

namespace App\PdfProcessing\Drivers;

use App\PdfProcessing\Contracts\Driver;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\Exceptions\PdfParsingException;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PaginatedDocumentContent;
use App\PdfProcessing\PdfDriver;
use App\PdfProcessing\PdfProcessingManager;
use Exception;
use Http\Client\Exception\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Throwable;

class CopilotPdfParserDriver implements Driver
{

    /*
    |--------------------------------------------------------------------------
    | Experimental
    |--------------------------------------------------------------------------
    |
    | This driver make use of an external service that is currently not open source.
    | The service assumes a specific kind of document and return a structure
    | for furher processing using machine learning techniques
    | (e.g. calculating embeddings)
    |
    */

    protected array $config;

    public function __construct(array $config = null)
    {

        if(! isset($config['host'])){
            throw new InvalidArgumentException('Host is required to create a CopilotPdfParser');
        }

        $this->config = $config;
    }


    public function text(DocumentReference $document): PaginatedDocumentContent
    {
        if(empty($document->url)){
            throw new InvalidArgumentException(__('The PDF driver is able to deal only with remote downloadable files'));
        }

        try{
            $response = Http::acceptJson()
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . '/extract-text', [
                    "mime_type" => $document->mimeType,
                    "url" => $document->url
                ])
                ->throw();

            $content = collect($response->json()['content'])->mapWithKeys(function($entry){
                return [$entry['metadata']['page_number'] => $entry['text']];
            });

            return new PaginatedDocumentContent($content->toArray());
        }
        catch(Throwable $ex)
        {
            // TODO: response body can contain error information // {"code":500,"message":"Error while parsing file","type":"Internal Server Error"}
            logs()->error("Error extracting text from document [{$document->url}]", ['error' => $ex->getMessage()]);
            throw new PdfParsingException('Unable to process the file', 1, $ex);
        }
        
    }

    public function properties(DocumentReference $reference): DocumentProperties
    {
        // This driver is not able to extract PDF metatata, 
        // fallback to a local first driver

        return Pdf::driver(PdfDriver::SMALOT_PDF->value)->properties($reference);
    }
}