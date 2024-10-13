<?php

namespace App\PdfProcessing;

use Carbon\Carbon;
use JsonSerializable;

class DocumentProperties implements JsonSerializable
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $author,
        public readonly int $pages,
        public readonly ?string $pageSize,
        public readonly ?bool $isTaggedPdf,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $modifiedAt,
        public readonly ?string $producedWith,

        )
    {
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
            'title' => $this->title,
            'description' => $this->description,
            'author' => $this->author,
            'pages' => $this->pages,
            'page_size' => $this->pageSize,
            'is_tagged_pdf' => $this->isTaggedPdf,
            'created_at' => $this->createdAt,
            'modified_at' => $this->modifiedAt,
            'produced_with' => $this->producedWith,
        ];
    }

}
