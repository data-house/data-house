<?php

namespace App\PdfProcessing\Drivers;

use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\PdfProcessingManager;
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

    public function text()
    {
        
    }

    public function thumbnail()
    {

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