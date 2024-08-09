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


    public function asStructured(): array
    {

        $pages = $this->collect()->map(function($page, $pageNumber){
            return [
                "category" => "page",
                "attributes" => [
                    "page" => $pageNumber
                ],
                "content" => [
                    [
                        "role" => "body",
                        "text" => $page,
                        "marks" => [],
                        "attributes" => [],
                    ]
                ]
            ];
        });

        return [
            "type" => "doc",
            "content" => $pages->toArray(),
        ];
    }

}
