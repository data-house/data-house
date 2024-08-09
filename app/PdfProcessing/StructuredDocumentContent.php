<?php

namespace App\PdfProcessing;

use Illuminate\Support\Arr;

class StructuredDocumentContent extends PaginatedDocumentContent
{

    public function isEmpty(): bool
    {
        if(empty($this->raw)){
            return true;
        }

        if(empty($this->raw['content'])){
            return true;
        }

        return collect($this->pages())->filter()->isEmpty();
    }

    /**
     * Return the whole document as plain text
     */
    public function all(): string
    {
        return collect($this->pages())->flatten()->join('\f');
    }

    /**
     * Return the pages that compose the document
     */
    public function pages(): array
    {
        if($this->isEmpty()){
            return [];
        }

        // page => text
        return collect($this->raw['content'])->mapWithKeys(function($page){
            return [$page['attributes']['page'] => collect($page['content'])->pluck('text')->join(' ')];
        })->toArray();
    }


    public function asStructured(): array
    {
        return $this->raw;
    }


}
