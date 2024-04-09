<?php

use App\Models\Visibility;

return [

    'default_document_visibility' => env('DOCUMENT_DEFAULT_VISIBILITY', Visibility::TEAM->name),


    'topics' => [
        'disk' => env('TOPIC_FILE_DISK', env('FILESYSTEM_DISK', 'local')),
        'file' => env('TOPIC_FILE_NAME', null),

        'schemes' => env('TOPIC_SCHEMES_ENABLED', null),
    ],


    'upload' => [
        'allow_direct_upload' => (bool)env('LIBRARY_UPLOAD_ALLOW_DIRECT_UPLOAD', true),
    ],


    'projects' => [
        'filterable_status' => env('PROJECTS_FILTERABLE_STATUS', null),
    ],

];
