<?php

namespace helpers;

use Illuminate\Support\Facades\Config;

class BaseCachedEntityTableMigration
{
    protected function getTableNameWithSchema(string $tableName): string
    {
        return join('.', [
            Config::get('database.connections.pgsql-entity-cache.search_path'),
            $tableName
        ]);
    }
}