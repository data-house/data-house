<?php

namespace App\DocumentConversion\Contracts;

use App\DocumentConversion\ConversionRequest;
use App\DocumentConversion\Format;
use App\Models\Document;
use App\PdfProcessing\DocumentProperties;

interface Convertible
{
    
    public function toConvertible(): ConversionRequest;

}