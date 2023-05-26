<?php

return [
    'pgsql_app_connection' => [
        'properties' => [
            'username' => env('PG_APP_USERNAME', ''),
            'password' => env('PG_APP_PASSWORD', env('DB_PASSWORD', '')),
            'schema' => env('PG_APP_SCHEMA', 'public'),
        ],
    ],
    'pgsql_sync_connection' => [
        'properties' => [
            'username' => env('PG_SYNC_USERNAME', ''),
            'password' => env('PG_SYNC_PASSWORD', env('DB_PASSWORD', '')),
            'schema' => env('PG_SYNC_SCHEMA', 'entity_cache'),
        ],
        'name' => env('PG_SYNC_CONNECTION_NAME', 'entity_sync')
    ],
    'pgsql_admin_connection' => [
        'name' => env('PG_ADMIN_CONNECTION_NAME', 'pgsql_main')
    ]
];