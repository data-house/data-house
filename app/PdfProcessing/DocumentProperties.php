<?php

namespace App\PdfProcessing;

use Carbon\Carbon;

class DocumentProperties
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


}
