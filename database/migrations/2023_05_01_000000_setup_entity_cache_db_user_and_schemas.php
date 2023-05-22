<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const DEFAULT_SCHEMA_NAME = 'public';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createSchemaIfNotExists($this->getDatabaseConnectionConfigValue('pgsql-entity-cache.search_path'));
        $this->createSchemaIfNotExists($this->getDatabaseConnectionConfigValue('pgsql-app.search_path'));

        $this->createDBUserWithAllPrivilegesOnSchema(
            $this->getDatabaseConnectionConfigValue('pgsql-app.username'),
            $this->getDatabaseConnectionConfigValue('pgsql-app.password'),
            $this->getDatabaseConnectionConfigValue('pgsql-app.search_path')
        );

        $this->createDBUserWithAllPrivilegesOnSchema(
            $this->getDatabaseConnectionConfigValue('pgsql-entity-cache.username'),
            $this->getDatabaseConnectionConfigValue('pgsql-entity-cache.password'),
            $this->getDatabaseConnectionConfigValue('pgsql-entity-cache.search_path')
        );

        $this->grantReadPrivilegesOnSchema(
            $this->getDatabaseConnectionConfigValue('pgsql-app.username'),
            $this->getDatabaseConnectionConfigValue('pgsql-entity-cache.search_path')
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropSchema($this->getDatabaseConnectionConfigValue('pgsql-entity-cache.search_path'));
        $this->dropSchema($this->getDatabaseConnectionConfigValue('pgsql-app.search_path'));

        $this->dropUser($this->getDatabaseConnectionConfigValue('pgsql-entity-cache.username'));
        $this->dropUser($this->getDatabaseConnectionConfigValue('pgsql-app.username'));

    }

    private function createSchemaIfNotExists(string $schemaName)
    {
        if ($schemaName === self::DEFAULT_SCHEMA_NAME) {
            return;
        }

        $isExists = filled(DB::select('SELECT 1 FROM information_schema.schemata WHERE schema_name = :schema', [
            'schema' => $schemaName,
        ]));

        if (! $isExists) {
            DB::statement("CREATE SCHEMA $schemaName");
            DB::statement("SET search_path TO $schemaName");
        }
    }

    private function dropSchema(string $schemaName)
    {
        if ($schemaName !== self::DEFAULT_SCHEMA_NAME) {
            DB::statement("DROP SCHEMA IF EXISTS $schemaName CASCADE");
        }
    }

    private function dropUser(string $username)
    {
        DB::statement("DROP USER IF EXISTS $username");
    }

    private function createDBUserWithAllPrivilegesOnSchema(string $username, string $password, string $schema)
    {
        DB::statement("CREATE USER $username WITH ENCRYPTED PASSWORD '$password'");
        DB::statement("GRANT ALL ON SCHEMA $schema TO $username");

        /**
         * Allows to set the privileges that applies to objects that already exist
         */
        DB::statement("GRANT ALL ON ALL TABLES IN SCHEMA $schema TO $username");
        DB::statement("GRANT ALL ON ALL SEQUENCES IN SCHEMA $schema TO $username");
        DB::statement("GRANT ALL ON ALL PROCEDURES IN SCHEMA $schema TO $username");
        DB::statement("GRANT ALL ON ALL ROUTINES IN SCHEMA $schema TO $username");
        DB::statement("GRANT ALL ON ALL FUNCTIONS IN SCHEMA $schema TO $username");

        /**
         * Allows to set the privileges that will be applied to objects created in the future
         */
        DB::statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON TABLES TO $username;");
        DB::statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON SEQUENCES TO $username;");
        DB::statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON TYPES TO $username;");
        DB::statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON FUNCTIONS TO $username;");
        DB::statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT ALL ON ROUTINES TO $username;");
    }

    private function grantReadPrivilegesOnSchema(string $username, string $schema)
    {
        DB::statement("GRANT USAGE ON SCHEMA $schema TO $username");
        /**
         * Allows to set the privileges that applies to objects that already exist
         */
        DB::statement("GRANT SELECT,REFERENCES ON ALL TABLES IN SCHEMA $schema TO $username");
        /**
         * Allows to set the privileges that will be applied to objects created in the future
         */
        DB::statement("ALTER DEFAULT PRIVILEGES IN SCHEMA $schema GRANT SELECT,REFERENCES ON TABLES TO $username;");
    }

    private function getDatabaseConnectionConfigValue(string $name)
    {
        $value = Config::get("database.connections.$name");
        // Ensure the env variable only contains alphanumeric characters and underscores
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            throw new InvalidArgumentException("Incorrect $name env variable value: '$value'");
        }

        return $value;
    }
};
