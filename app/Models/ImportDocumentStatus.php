<?php

namespace App\Models;


enum ImportDocumentStatus: int
{
    /**
     * Waiting to download and process the document
     */
    case PENDING = 10;

    /**
     * Document is being imported from source disk
     */
    case IMPORTING = 20;

    /**
     * Document imported
     */
    case COMPLETED = 30;

    /**
     * Import cancelled for document
     */
    case CANCELLED = 40;

    /**
     * Import cancelled for document as user doesn't have permissions
     */
    case CANCELLED_MISSING_PERMISSION = 41;

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
    case SKIPPED_DIFFERENT_VERSION = 53;

    /**
     * Import failed, no clear reason
     */
    case FAILED = 60;

    /**
     * Import failed as consequence of a network transfer error
     */
    case FAILED_DOWNLOAD_ERROR = 61;


    public function label(): string
    {
        return match ($this) {
            self::CANCELLED_MISSING_PERMISSION => __('Missing permission'),
            self::SKIPPED_MISSING_SOURCE => __('Missing source'),
            self::SKIPPED_DUPLICATE => __('Duplicate'),
            self::SKIPPED_DIFFERENT_VERSION => __('Different version'),
            self::FAILED_DOWNLOAD_ERROR => __('Download error'),
            default => str($this->name)->title()->toString(),
        };
    }

    public function style(): ?string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::IMPORTING => 'pending',
            self::COMPLETED => 'success',
            self::CANCELLED => 'cancel',
            self::FAILED => 'failure',
            default => null,
        };
    }
    
}
