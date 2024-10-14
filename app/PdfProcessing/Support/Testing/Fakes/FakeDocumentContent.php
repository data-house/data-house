<?php

namespace App\PdfProcessing\Support\Testing\Fakes;

use App\PdfProcessing\DocumentContent;
use OneOffTech\Parse\Client\DocumentFormat\DocumentNode;

class FakeDocumentContent extends DocumentContent
{

    /**
     * Create a fake content from a line of text
     */
    public static function fromString(string $text): static
    {
        return new self($text);
    }
    
    /**
     * Create a fake content from different pages
     */
    public static function fromPages(array $pages): static
    {
        $pageNodes = collect($pages)->map(function($page, $pageNumber){
            return [
                'category' => 'page',
                'attributes' => [
                    'page' => $pageNumber,
                ],
                'content' => [
                    [
                        'category' => 'body',
                        'content' => $page,
                        'marks' => [],
                        'attributes' => [],
                    ],
                ],
            ];
        });

        $doc = [
            'category' => 'doc',
            'attributes' => null,
            'content' => $pageNodes->toArray(),
        ];

        return new self(DocumentNode::fromArray($doc));
    }

}
