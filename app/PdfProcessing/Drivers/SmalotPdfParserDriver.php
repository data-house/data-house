<?php

namespace App\PdfProcessing\Drivers;

use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\PdfProcessingManager;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Process;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class SmalotPdfParserDriver
{
    protected Parser $parser;

    public function __construct(array $config = null)
    {
        $this->buildParser();
    }

    protected function buildParser()
    {
        $config = new Config();
        $config->setFontSpaceLimit(-60);
        $config->setRetainImageContent(false);

        $parser = new Parser([], $config);

        $this->parser = $parser;
    }

    public function text($path): string
    {
        try{
            // using iconv to re-encode UTF-8 strings ignoring illegal characters that might cause failures
            $content = iconv('UTF-8', 'UTF-8//IGNORE', $this->parser->parseFile($path)->getText());

            if($content === false){
                throw new Exception("Failed to perform UTF-8 encoding");
            }

            return $content;
        }
        catch(Exception $ex)
        {
            logs()->error("Error extracting text from document [{$path}]", ['error' => $ex->getMessage()]);
            throw $ex;
        }
        
    }

    public function info($path): DocumentProperties
    {
        $pdf = $this->parser->parseFile($path);

        $attributes = $pdf->getDetails();

        $createdAt = $attributes['CreationDate'] ?? null;
        $modifiedAt = $attributes['ModDate'] ?? null;

        return new DocumentProperties(
            title:  str($attributes['Title'] ?? '')->utf8(),
            description:  str($attributes['Subject'] ?? '')->utf8(),
            author:  str($attributes['Author'] ?? null)->utf8(),
            pageSize: null, 
            pages: ($attributes['Pages'] ?? false) ? (int)$attributes['Pages'] : null, 
            isTaggedPdf: null, 
            createdAt: Carbon::parse($createdAt), 
            modifiedAt: Carbon::parse($modifiedAt), 
            producedWith: str($attributes['Creator'] ?? null)->utf8(),  //creator software
        );
    }

}