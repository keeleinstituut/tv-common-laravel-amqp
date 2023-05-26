<?php

namespace Amqp\Database\Helpers;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class BaseCachedEntityTableMigration extends Migration
{
    public function getConnection(): ?string
    {
        return Config::get('sync.pgsql_sync_connection.name', $this->connection);
    }
}
