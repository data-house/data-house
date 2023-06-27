<?php

namespace App\PdfProcessing;

use Illuminate\Support\Arr;

class PaginatedDocumentContent extends DocumentContent
{


    /**
     * Return the pages that compose the document
     */
    public function pages(): array
    {
        if(!is_array($this->raw)){
            return Arr::wrap($this->raw);
        }

        return $this->raw;
    }

}
