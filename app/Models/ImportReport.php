<?php

namespace App\Models;


class ImportReport
{
    public function __construct(
        public int $processedCount,
        public $nextPageToken = null
    )
    {
        
    }
}
