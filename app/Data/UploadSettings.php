<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class UploadSettings extends Data
{
    public function __construct(
        public string $uploadLinkUrl,
        public bool $supportProjects = false,
        public ?string $limitProjectsTo = null,
    ) {
    }
}
