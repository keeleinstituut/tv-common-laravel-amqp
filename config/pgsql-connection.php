<?php

/**
 * The file contains the configuration of 2 different connections to the same database but with different users and schemas.
 * The functionality is needed to make cached tables read-only for the app user and writable for the sync user.
 */
return [
    /*
     * PGSQL connection that will be used by the app. The connection will have readonly access to cached entities schema.
     */
    'app' => [
        'properties' => [
            'username' => env('PG_APP_USERNAME', ''),
            'password' => env('PG_APP_PASSWORD', env('DB_PASSWORD', '')),
            'schema' => env('PG_APP_SCHEMA', 'public'),
        ],
    ],
    /*
     * PGSQL connection that will be used for sync of cached entities
     */
    'sync' => [
        'properties' => [
            'username' => env('PG_SYNC_USERNAME', ''),
            'password' => env('PG_SYNC_PASSWORD', env('DB_PASSWORD', '')),
            'schema' => env('PG_SYNC_SCHEMA', 'entity_cache'),
        ],
        'name' => env('PG_SYNC_CONNECTION_NAME', 'entity_sync'),
    ],
    /**
     * @deprecated
     * @see DbSchemasSetupCommand
     * Admin PGSQL connection with user that have privileges for creation users and schemas.
     */
    'admin' => [
        'name' => env('PG_ADMIN_CONNECTION_NAME', 'pgsql_main'),
    ],
];
