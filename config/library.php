<?php

use App\Models\Document;
use App\Models\Visibility;

return [

    'default_document_visibility' => env('DOCUMENT_DEFAULT_VISIBILITY', Visibility::TEAM->name),


    'upload' => [
        'allow_direct_upload' => (bool)env('LIBRARY_UPLOAD_ALLOW_DIRECT_UPLOAD', true),
    ],


    'projects' => [
        'signature' => (bool)env('PROJECTS_SIGNATURE_ENABLED', true),
        'period' => (bool)env('PROJECTS_PERIOD_ENABLED', true),

        'filterable_status' => env('PROJECTS_FILTERABLE_STATUS', null),
    ],

    Document::class => [
        'sorting' => [
            'default' => '-recently_added',
            'allowed' => [
                'title' => 'title',
                '-recently_added' => 'created_at', // the minus prefix indicates the default direction
            ],
        ],
    ],

];
