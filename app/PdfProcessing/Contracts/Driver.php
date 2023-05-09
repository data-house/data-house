<?php

namespace App\PdfProcessing\Contracts;

use App\PdfProcessing\DocumentProperties;

interface Driver
{

    /**
     * Get PDF document properties
     * 
     * @param string $path The file absolute path
     */
    public function properties($path): DocumentProperties;
}