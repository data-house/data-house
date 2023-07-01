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
    case SMALOT_PDF = 'smalot';

    /**
     * The local driver implemented using command line process invokation
     */
    case XPDF = 'xpdf';

    /**
     * The remote experimental driver
     */
    case COPILOT = 'copilot';
}