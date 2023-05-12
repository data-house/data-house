<?php

namespace App\PdfProcessing;


/**
 * 
 */
enum PdfDriver: string
{
    case SMALOT_PDF = 'smalot';
    case XPDF = 'xpdf';
}