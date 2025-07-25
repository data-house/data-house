<?php

use App\Models\CatalogEntry;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Note;
use App\Models\Project;
use App\Models\Question;
use App\Models\SkosConcept;
use App\Models\Star;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => true,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Meilisearch settings. Meilisearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own Meilisearch installation.
    |
    | See: https://docs.meilisearch.com/guides/advanced_guides/configuration.html
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            Document::class => [
                'filterableAttributes'=> [
                    'id',
                    'draft',
                    'languages',
                    'mime',
                    'type',
                    'format',
                    'team_id',
                    'team_name',
                    'project_id',
                    'uploaded_by',
                    'visibility',
                    'published',
                    'published_at',
                    'project_countries',
                    'project_region',
                    'project_topics',
                    'library_collections',
                    'stars',
                    'concepts',
                ],
                'sortableAttributes' => [
                    'title',
                    'updated_at',
                    'created_at',
                    'published_at',
                    'team_id',
                    'project_id',
                ],
            ],
            Project::class => [
                'filterableAttributes'=> [
                    'type',
                    'countries',
                    'region',
                    'topics',
                    'organizations',
                    'status',
                    'starts_at',
                    'ends_at',
                ],
                'sortableAttributes' => [
                    'title',
                    'created_at',
                    'starts_at',
                    'ends_at',
                ],
                'typoTolerance' => [
                    'disableOnAttributes' => ['slug'],
                ],
            ],
            Question::class => [
                'filterableAttributes'=> [
                    'type',
                    'target',
                    'language',
                    'user_id',
                    'author',
                    'team_id',
                    'team_name',
                    'visibility',
                    'questionable_id',
                    'questionable_type',
                ],
            ],
            Star::class => [
                'filterableAttributes'=> [
                    'id',
                    'user_id',
                ],
                'sortableAttributes' => [
                    'created_at',
                ],
            ],
            Note::class => [
                'filterableAttributes'=> [
                    'id',
                    'user_id',
                    'linked_resource_type',
                ],
                'sortableAttributes' => [
                    'created_at',
                ],
            ],
            Collection::class => [
                'filterableAttributes'=> [
                    'id',
                    'visibility',
                    'user_id',
                    'team_id',
                    'topic_group',
                ],
                'sortableAttributes' => [
                    'title',
                    'created_at',
                    'updated_at',
                    'topic_group',
                ],
            ],
            SkosConcept::class => [
                'filterableAttributes'=> [
                    'id',
                    'scheme_id',
                    'top_concept',
                ],
                'sortableAttributes' => [
                    'pref_label',
                    'notation',
                    'scheme_pref_label',
                    'top_concept',
                ],
            ],
            CatalogEntry::class => [
                'filterableAttributes'=> [
                    'id',
                    'entry_index',
                    'catalog_id',
                    'document_id',
                    'project_id',
                    'created_at',
                    'trashed_at',
                ],
                'sortableAttributes' => [
                    'entry_index',
                    'catalog_id',
                    'document_id',
                    'project_id',
                    'created_at',
                    'trashed_at',
                ],
            ],
        ],
    ],

];
