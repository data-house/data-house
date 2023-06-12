<?php

namespace App\DocumentConversion\Contracts;

use App\DocumentConversion\ConversionRequest;
use App\DocumentConversion\ConvertedFile;
use App\DocumentConversion\Format;
use App\Models\Document;
use App\PdfProcessing\DocumentProperties;

interface Driver
{
    /**
     * Convert a document into the format specified
     * 
     * @param string $url The downloadable file (some drivers might support a local file)
     * @return string The file path in the conversion disk
     */
    public function convert(Convertible $model, Format $format): ConvertedFile;


    /**
     * The supported conversions
     * 
     * @return array<string, array<\App\DocumentConversion\Format>> return the supported mime types, as input, and the conversion format for each type
     */
    public static function supports(): array;
}