<?php

namespace Amqp\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DbSchemasSetupCommand extends Command
{
    protected $signature = 'db-schema:setup';

    protected $description = 'Setup DB schema for cached entities';

    public function handle(): void
    {
        $this->createSchemaIfNotExists(Config::get('sync.pgsql_sync_connection.properties.schema'));
        $this->createSchemaIfNotExists(Config::get('sync.pgsql_app_connection.properties.schema'));

        $this->createDBUserIfNotExistsWithAllPrivilegesOnSchema(
            Config::get('sync.pgsql_app_connection.properties.username'),
            Config::get('sync.pgsql_app_connection.properties.password'),
            Config::get('sync.pgsql_app_connection.properties.schema')
        );

        $this->createDBUserIfNotExistsWithAllPrivilegesOnSchema(
            Config::get('sync.pgsql_sync_connection.properties.username'),
            Config::get('sync.pgsql_sync_connection.properties.password'),
            Config::get('sync.pgsql_sync_connection.properties.schema')
        );

        $this->grantReadPrivilegesOnSchema(
            Config::get('sync.pgsql_app_connection.properties.username'),
            Config::get('sync.pgsql_sync_connection.properties.schema'),
            Config::get('sync.pgsql_sync_connection.properties.username')
        );
    }

    private function createSchemaIfNotExists(string $schemaName): void
    {
        $this->connection()->statement("CREATE SCHEMA IF NOT EXISTS $schemaName");
        $this->connection()->statement("SET search_path TO $schemaName");
    }

    private function createDBUserIfNotExistsWithAllPrivilegesOnSchema(string $username, string $password, string $schema): void
    {
        $isExists = filled(
            $this->connection()->select("SELECT * FROM pg_catalog.pg_user WHERE usename = :username", [
                'username' => $username
            ])
        );

        if (!$isExists) {
            $this->connection()->statement("CREATE USER $username WITH ENCRYPTED PASSWORD '$password'");
        }

        $this->connection()->statement("GRANT ALL ON SCHEMA $schema TO $username");

        /**
         * Allows to set the privileges that applies to objects that already exist
         */
        $this->connection()->statement("GRANT ALL ON ALL TABLES IN SCHEMA $schema TO $username");
        $this->connection()->statement("GRANT ALL ON ALL SEQUENCES IN SCHEMA $schema TO $username");
        $this->connection()->statement("GRANT ALL ON ALL PROCEDURES IN SCHEMA $schema TO $username");
        $this->connection()->statement("GRANT ALL ON ALL ROUTINES IN SCHEMA $schema TO $username");
        $this->connection()->statement("GRANT ALL ON ALL FUNCTIONS IN SCHEMA $schema TO $username");

        /**
         * Allows to set the privileges that will be applied to objects created in the future
         */
        $this->connection()->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON TABLES TO $username;");
        $this->connection()->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON SEQUENCES TO $username;");
        $this->connection()->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON TYPES TO $username;");
        $this->connection()->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON FUNCTIONS TO $username;");
        $this->connection()->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON ROUTINES TO $username;");
    }

    private function grantReadPrivilegesOnSchema(string $username, string $schema, string $schemaMainUser): void
    {
        $this->connection()->statement("GRANT USAGE ON SCHEMA $schema TO $username");
        /**
         * Allows to set the privileges that applies to objects that already exist
         */
        $this->connection()->statement("GRANT SELECT, REFERENCES ON ALL TABLES IN SCHEMA $schema TO $username");
        $this->connection()->statement("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA $schema TO $username");
        /**
         * Allows to set the privileges that will be applied to objects created in the future
         */
        $this->connection()->statement("ALTER DEFAULT PRIVILEGES FOR USER $schemaMainUser IN SCHEMA $schema GRANT SELECT, REFERENCES ON TABLES TO $username;");
        $this->connection()->statement("ALTER DEFAULT PRIVILEGES FOR USER $schemaMainUser IN SCHEMA $schema GRANT SELECT, USAGE ON SEQUENCES TO $username;");
    }

    private function connection(): Connection
    {
        return DB::connection(Config::get('sync.pgsql_admin_connection'));
    }
}