<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class TeamSettings extends Data
{
    public function __construct(
        public ?UploadSettings $upload = null,
    ) {
    }
}
