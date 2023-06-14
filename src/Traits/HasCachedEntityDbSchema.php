<?php

namespace SyncTools\Traits;

trait HasCachedEntityDbSchema
{
    public function getTable(): string
    {
        return config('pgsql-connection.sync.properties.schema').'.'.parent::getTable();
    }
}
