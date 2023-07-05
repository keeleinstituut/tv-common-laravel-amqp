<?php

namespace SyncTools\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

trait IsCachedEntity
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

    /**
     * The method is needed in case of accessing related entities od cached entity.
     * Laravel pass the model connection to the related entities but in case of
     * passing sync connection to regular models it will end up with exception.
     */
    protected function newRelatedInstance($class)
    {
        return tap(new $class, function (Model $instance) use ($class) {
            if (! $instance->getConnectionName()) {
                $isTryingToAssignSyncConnectionToRegularModel =
                    $this->connection === config('pgsql-connection.sync.name') &&
                    ! in_array(IsCachedEntity::class, class_uses_recursive($class), true);

                if ($isTryingToAssignSyncConnectionToRegularModel) {
                    $instance->setConnection(config('database.default'));
                } else {
                    $instance->setConnection($this->connection);
                }
            }
        });
    }

    /**
     * Get the current connection name for the model.
     * For the testing env the function will make read-only models editable.
     */
    public function getConnectionName(): ?string
    {
        if (empty($this->connection) && App::environment('testing')) {
            return config('pgsql-connection.sync.name');
        }

        return $this->connection;
    }
}
