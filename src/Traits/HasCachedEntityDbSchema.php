<?php

namespace SyncTools\Traits;

use Illuminate\Support\Str;

trait HasCachedEntityDbSchema
{
    public function getTable(): string
    {
        $tableName = parent::getTable();
        $schemaPrefix = config('pgsql-connection.sync.properties.schema').'.';

        if (Str::startsWith($tableName, $schemaPrefix)) {
            return $tableName;
        }

        return $schemaPrefix.$tableName;
    }
}
