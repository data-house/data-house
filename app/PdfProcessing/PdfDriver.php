<?php

namespace App\PdfProcessing;


/**
 * 
 */
enum PdfDriver: string
{   
    /**
     * The native driver implemented in PDF
     */
    case SMALOT = 'smalot';

    /**
     * The PDF Text Extractor service driver
     */
    case PARSE = 'parse';
}