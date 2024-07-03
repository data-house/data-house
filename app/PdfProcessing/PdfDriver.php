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
     *
     * @deprecated XPDF driver will be removed in a future version
     */
    case XPDF = 'xpdf';

    /**
     * The PDF Text Extractor service driver
     */
    case EXTRACTOR_SERVICE = 'extractor';
}