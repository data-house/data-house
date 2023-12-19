<?php

use App\Models\Visibility;

return [

    'default_document_visibility' => env('DOCUMENT_DEFAULT_VISIBILITY', Visibility::TEAM->name),

];
