<?php

namespace helpers;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class BaseCachedEntityTableMigration extends Migration
{
    protected function getTableNameWithSchema(string $tableName): string
    {
        return join('.', [
            Config::get('database.connections.pgsql-entity-cache.search_path'),
            $tableName
        ]);
    }
}