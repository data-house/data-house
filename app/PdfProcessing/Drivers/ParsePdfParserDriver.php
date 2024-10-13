<?php

namespace App\PdfProcessing\Drivers;

use Throwable;
use InvalidArgumentException;
use App\PdfProcessing\PdfDriver;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Contracts\Driver;
use App\PdfProcessing\DocumentReference;
use OneOffTech\Parse\Client\ParseOption;
use App\PdfProcessing\DocumentProperties;
use OneOffTech\Parse\Client\DocumentProcessor;
use App\PdfProcessing\Exceptions\PdfParsingException;
use OneOffTech\Parse\Client\Connectors\ParseConnector;

class ParsePdfParserDriver implements Driver
{
    protected readonly ParseConnector $client;

    protected readonly DocumentProcessor $defaultProcessor;

    public function __construct(array $config = null)
    {
        if(blank($config['host'] ?? null)){
            throw new InvalidArgumentException('Host required for Parse driver.');
        }

        $this->defaultProcessor = DocumentProcessor::from($config['processor'] ?? DocumentProcessor::PYMUPDF->value);

        $this->client = new ParseConnector(
            token: $config['token'] ?? null,
            baseUrl: $config['host'],
        );
    }


    public function text(DocumentReference $document): DocumentContent
    {
        if(!$document->isRemote()){
            throw new InvalidArgumentException(__('Expected remote document. Local file given.'));
        }

        try{
            $parsedDocument = $this->client->parse(
                url: $document->url,
                mimeType: $document->mimeType,
                options: new ParseOption($this->defaultProcessor)
            );
            
            return new DocumentContent($parsedDocument->document());
        
        }
        catch(Throwable $ex)
        {
            logs()->error("Error extracting text from document [{$document->url}]", ['error' => $ex->getMessage()]);
            throw new PdfParsingException('Unable to process the file', $ex->getCode(), $ex);
        }
        
    }

    public function properties(DocumentReference $reference): DocumentProperties
    {
        // Fallback to the Smalot driver as Parse
        // does not return PDF metadata

        return Pdf::driver(PdfDriver::SMALOT)->properties($reference);
    }
}