<?php

use App\Models\Author;
use App\Models\Mod;
use App\Models\ModVersion;
use App\Models\Report;

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
    | Supported: "algolia", "meilisearch", "typesense",
    |            "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    'limit' => [
        'max' => [
            'filament' => (int) env('SCOUT_FILAMENT_LIMIT', 20),
        ],
    ],
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            Mod::class => [
                'typoTolerance' => [
                    'enabled' => true,
                    'disableOnAttributes' => [
                    ],
                    'minWordSizeForTypos' => [
                        'oneTypo' => 3,
                        'twoTypos' => 5,
                    ],
                ],
                'searchableAttributes' => [
                    'name',
                    'title',
                    'summary',
                    'description',
                    'owner',
                ],
                'filterableAttributes' => [
                    'category',
                    'downloads_count',
                    'popularity',
                    'score',
                    'latest_version',
                    'factorio_version',
                    'tags',
                ],
                'sortableAttributes' => [
                    'name',
                    'title',
                    'owner',
                    'category',
                    'downloads_count',
                    'popularity',
                    'score',
                    'latest_version',
                    'factorio_version',
                ],
            ],

            Author::class => [
                'typoTolerance' => [
                    'enabled' => true,
                    'minWordSizeForTypos' => [
                        'oneTypo' => 3,
                        'twoTypos' => 5,
                    ],
                ],
                'searchableAttributes' => [
                    'name',
                ],
                'filterableAttributes' => [
                ],
                'sortableAttributes' => [
                    'name',
                ],
            ],

            ModVersion::class => [
                'typoTolerance' => [
                    'enabled' => true,
                    'minWordSizeForTypos' => [
                        'oneTypo' => 3,
                        'twoTypos' => 5,
                    ],
                ],
                'searchableAttributes' => [
                    'mod_name',
                    'version',
                    'file_name',
                    'factorio_version',
                ],
                'filterableAttributes' => [
                    'mod_id',
                    'mod_name',
                    'factorio_version',
                ],
                'sortableAttributes' => [
                    'version',
                    'factorio_version',
                    'released_at',
                    'mod_name',
                ],
            ],

            Report::class => [
                'typoTolerance' => [
                    'enabled' => true,
                    'minWordSizeForTypos' => [
                        'oneTypo' => 3,
                        'twoTypos' => 5,
                    ],
                ],
                'searchableAttributes' => [
                    'mod_name',
                    'mod_version',
                    'sha1',
                ],
                'filterableAttributes' => [
                    'mod_name',
                    'mod_version',
                    'score',
                ],
                'sortableAttributes' => [
                    'mod_name',
                    'mod_version',
                    'score',
                ],
            ],
        ],

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

        'queue' => 'scout',

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
            'searchable' => 100,
            'unsearchable' => 100,
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
    ],
];
