<?php

/**
 * The file contains the configuration of 3 different connections to the same database but with different users and schemas.
 * The functionality is needed to make cached tables read-only for the app user and writable for the sync user.
 * Also, the configuration contains an admin PGSQL user which is needed to create additional users and schemas inside the database.
 * @see DbSchemasSetupCommand
 */
return [
    /*
     * Main application PGSQL connection.
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
    /*
     * Admin PGSQL connection with user that have privileges for creation users and schemas.
     */
    'admin' => [
        'name' => env('PG_ADMIN_CONNECTION_NAME', 'pgsql_main'),
    ],
];
