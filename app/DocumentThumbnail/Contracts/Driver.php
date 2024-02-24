<?php

namespace App\DocumentThumbnail\Contracts;

use App\DocumentThumbnail\ConversionRequest;
use App\DocumentThumbnail\FileThumbnail;
use App\DocumentThumbnail\Format;
use App\Models\Document;
use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;

interface Driver
{
    /**
     * Generate a thumbnail of the given document
     * 
     * @param string $url The downloadable file (some drivers might support a local file)
     * @return string The file path in the conversion disk
     */
    public function thumbnail(DocumentReference $reference): FileThumbnail;


    /**
     * The supported input formats
     * 
     * @return array<string, array<\App\DocumentConversion\Format>> return the supported mime types, as input, and the conversion format for each type
     */
    public static function supports(): array;
}