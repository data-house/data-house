<?php

namespace App\PdfProcessing\Drivers;

use App\PdfProcessing\Contracts\Driver;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\PdfProcessingManager;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;

class XpdfDriver implements Driver
{

    private const PDF_INFO_BINARY = 'pdfinfo';

    private const PDF_TO_TEXT_BINARY = 'pdftotext';

    public function __construct()
    {
        
    }


    public function text(DocumentReference $document): DocumentContent
    {
        if(empty($document->path)){
            throw new InvalidArgumentException(__('The PDF driver is able to deal only with local files'));
        }


        $temp_file = tempnam(sys_get_temp_dir(), 'dh-xpdf');

        try{

            $result = Process::run(self::PDF_TO_TEXT_BINARY . ' -enc UTF-8 -layout ' . $document->path . ' ' . $temp_file);

            if(!$result->successful()){
                throw new Exception("pdf to text execution error " . $result->errorOutput());
            }
            
            $output = file_get_contents($temp_file);

            // using iconv to re-encode UTF-8 strings ignoring illegal characters that might cause failures
            $content = iconv('UTF-8', 'UTF-8//IGNORE', $output);

            if($content === false){
                throw new Exception("Failed to perform UTF-8 encoding");
            }

            return new DocumentContent($content);
        }
        catch(Exception $ex)
        {
            logs()->error("Error extracting text from document [{$document->path}]", ['error' => $ex->getMessage()]);
            throw $ex;
        }
        finally {
            unlink($temp_file);
        }
        
    }

    public function properties(DocumentReference $document): DocumentProperties
    {
        if(empty($document->path)){
            throw new InvalidArgumentException(__('The PDF driver is able to deal only with local files'));
        }
        
        $result = Process::run(self::PDF_INFO_BINARY . ' -meta -rawdates ' . $document->path);
        
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


    public static function hasDependenciesInstalled(): bool
    {
        $pdfinfoCheck = Process::run(self::PDF_INFO_BINARY . ' -v');

        $pdftotextCheck = Process::run(self::PDF_TO_TEXT_BINARY . ' -v');

        return ($pdfinfoCheck->successful() || $pdfinfoCheck->exitCode() === 99)
            && ($pdftotextCheck->successful() || $pdftotextCheck->exitCode() === 99);
    }

}