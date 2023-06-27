<?php

namespace App\PdfProcessing\Contracts;

use App\PdfProcessing\DocumentProperties;
use App\PdfProcessing\DocumentReference;

interface Driver
{
    /**
     * Get text contained in the PDF
     * 
     * @param \App\PdfProcessing\DocumentReference $document The reference to the document. Could be either an absolute path or a url.
     * @return 
     */
    public function text(DocumentReference $document): string;

    /**
     * Get PDF document properties
     * 
     * @param \App\PdfProcessing\DocumentReference $document The reference to the document. Could be either an absolute path or a url.
     */
    public function properties(DocumentReference $document): DocumentProperties;
}