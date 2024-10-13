<?php

namespace App\PdfProcessing;

use Countable;
use JsonSerializable;
use Illuminate\Support\Collection;
use OneOffTech\Parse\Client\DocumentFormat\DocumentNode;

class DocumentContent implements JsonSerializable, Countable
{

    public readonly DocumentNode $raw;

    public function __construct(DocumentNode|string $raw)
    {
        $this->raw = $raw instanceof DocumentNode ? $raw : $this->stringToDocumentNode($raw);
    }

    protected function stringToDocumentNode(string $value): DocumentNode
    {
        return DocumentNode::fromArray([
            "category" => "doc",
            "content" => [[
                "category" => "page",
                "attributes" => [
                    "page" => 1
                ],
                "content" => [
                    [
                        "role" => "body",
                        "text" => $value,
                        "marks" => [],
                        "attributes" => [],
                    ]
                ]
            ]],
        ]);
    }

    /**
     * @return \OneOffTech\Parse\Client\DocumentFormat\PageNode[]
     */
    public function pages(): array
    {
        return $this->raw->pages();
    }

    /**
     * The number of pages in this document
     */
    public function count(): int
    {
        return $this->raw->count();
    }

    /**
     * Get the underlying document node
     */
    public function document(): DocumentNode
    {
        return $this->raw;
    }

    /**
     * Return the whole document as plain text
     */
    public function all(): string
    {
        return $this->raw->text();
    }

    public function collect(): Collection
    {
        return collect($this->pages());
    }

    /**
     * The document does not contain text. 
     * However It can be composed by images
     */
    public function isEmpty(): bool
    {
        return $this->raw->isEmpty();
    }
    
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): mixed
    {
        return [
            'raw' => $this->raw,
        ];
    }

    public function asStructured(): array
    {
        return [
            "category" => "doc",
            "attributes" => null,
            "content" => $this->raw->content,
        ];
    }

}
