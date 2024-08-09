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
use App\PdfProcessing\StructuredDocumentContent;

class ExtractorServicePdfParserDriver implements Driver
{

    protected const EXTRACT_ENDPOINT = '/extract-text';

    protected array $config;

    public function __construct(array $config = null)
    {
        if(! isset($config['host'])){
            throw new InvalidArgumentException('Host is required to create an Extractor Service PDF Parser');
        }

        $this->config = array_merge(['driver' => 'pymupdf'], $config);
    }


    public function text(DocumentReference $document): StructuredDocumentContent
    {
        if(empty($document->url)){
            throw new InvalidArgumentException(__('The PDF driver is able to deal only with remote downloadable files'));
        }

        try{
            $response = Http::acceptJson()
                ->asJson()
                ->post(rtrim($this->config['host'], '/') . self::EXTRACT_ENDPOINT, [
                    "mime_type" => $document->mimeType,
                    "url" => $document->url,
                    "driver" => $this->config['driver'],
                ])
                ->throw();

            $documentContent = $response->json();

            if(is_null($documentContent['type'] ?? null)){
                throw new PdfParsingException('Empty response from PDF service');
            }
            
            return new StructuredDocumentContent($documentContent);
        
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