<?php

use App\Models\Visibility;

return [

    'default_document_visibility' => env('DOCUMENT_DEFAULT_VISIBILITY', Visibility::TEAM->name),


    'topics' => [
        'disk' => env('TOPIC_FILE_DISK', env('FILESYSTEM_DISK', 'local')),
        'file' => env('TOPIC_FILE_NAME', null),
    ],

];
