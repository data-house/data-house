<?php

namespace App\PdfProcessing\Drivers;

use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\PdfProcessingManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Process;

class XpdfDriver
{

    private const PDF_INFO_BINARY = 'pdfinfo.exe';

    public function __construct()
    {
        
    }


    public function text()
    {
        
    }

    public function thumbnail()
    {

    }

    public function info($path): DocumentProperties
    {
        $result = Process::run(self::PDF_INFO_BINARY . ' -meta -rawdates ' . $path);
        
        $output = $result->output();

        $attributes = collect(str($output)
            ->beforeLast("Metadata:")
            ->trim()
            ->split("/(\r\n|\n|\r)/"))
            ->mapWithKeys(function($entry){

                $key = str($entry)->before(':')->trim()->toString();
                $value = str($entry)->after(':')->trim()->toString();

                return [$key => $value];
            })
            ->filter();

        // TODO: decide on the additional available properties
        // "Form" => "none"
        // "Encrypted" => "no"
        // "File size" => "70610 bytes"
        // "Optimized" => "no"
        // "PDF version" => "1.7"
        
        $createdAt = $attributes['CreationDate'] ?? null;
        $modifiedAt = $attributes['ModDate'] ?? null;

        return new DocumentProperties(
            title: str($attributes['Title'] ?? '')->utf8(),
            description:  str($attributes['Subject'] ?? '')->utf8(),
            author:  str($attributes['Author'] ?? null)->utf8(),
            pageSize: str($attributes['Page size'] ?? null)->utf8(),
            pages: ($attributes['Pages'] ?? false) ? (int)$attributes['Pages'] : null, 
            isTaggedPdf: $attributes['Tagged'] ?? null, 
            createdAt: Carbon::parse($createdAt), 
            modifiedAt: Carbon::parse($modifiedAt), 
            producedWith: str($attributes['Creator'] ?? null)->utf8(),  //creator software
        );

    }

}