<?php

namespace App\PdfProcessing\Drivers;

use Throwable;
use InvalidArgumentException;
use App\PdfProcessing\PdfDriver;
use App\PdfProcessing\Facades\Pdf;
use Illuminate\Support\Facades\Http;
use App\PdfProcessing\Contracts\Driver;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\PaginatedDocumentContent;
use App\PdfProcessing\Exceptions\PdfParsingException;

class ExtractorServicePdfParserDriver implements Driver
{

    protected const EXTRACT_ENDPOINT = '/extract-text';

    protected array $config;

    public function __construct(array $config = null)
    {

        if(! isset($config['host'])){
            throw new InvalidArgumentException('Host is required to create an Extractor Service PDF Parser');
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
                ->post(rtrim($this->config['host'], '/') . self::EXTRACT_ENDPOINT, [
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
        // fallback to the local native driver

        return Pdf::driver(PdfDriver::SMALOT_PDF->value)->properties($reference);
    }
}