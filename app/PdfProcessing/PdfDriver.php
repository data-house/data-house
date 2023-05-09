<?php

namespace App\PdfProcessing\Facades;


/**
 * 
 */
enum PdfDriver: string
{
    case SMALOT_PDF = 'smalot';
    case XPDF = 'xpdf';
}