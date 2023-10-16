<?php

namespace App\Models;


enum ImportDocumentStatus: int
{
    /**
     * Waiting to download and process the document
     */
    case PENDING = 10;

    /**
     * Document is downloading from source disk
     */
    case DOWNLOADING = 20;

    /**
     * Document imported
     */
    case COMPLETED = 30;

    /**
     * Import cancelled for document
     */
    case CANCELLED = 40;

    /**
     * Import skipped for document, no reason
     */
    case SKIPPED = 50;

    /**
     * Import skipped as source path does not exists
     */
    case SKIPPED_MISSING_SOURCE = 51;
    
    /**
     * Import skipped as the same file already exists in the Digital Library
     */
    case SKIPPED_DUPLICATE = 52;

    
    /**
     * Import skipped as the same file appear to be existing in a different version in the Digital Library
     */
    case SKIPPED_DIFFERENT_VERSION = 62;

    /**
     * Import failed, no clear reason
     */
    case FAILED = 60;

    /**
     * Import failed as consequence of a network transfer error
     */
    case FAILED_DOWNLOAD_ERROR = 61;
    
}
