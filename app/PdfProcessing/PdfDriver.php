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
     * The local driver implemented using XPDF command line
     */
    case XPDF = 'xpdf';

    /**
     * The remote experimental driver
     * 
     * @deprecated use @see PdfDriver::EXTRACTOR_SERVICE
     */
    case COPILOT = 'copilot';

    /**
     * The PDF Text Extractor service driver
     */
    case EXTRACTOR_SERVICE = 'extractor';
}