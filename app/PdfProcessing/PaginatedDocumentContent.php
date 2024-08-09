<?php

namespace App\PdfProcessing;

use Illuminate\Support\Arr;

class PaginatedDocumentContent extends DocumentContent
{


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
