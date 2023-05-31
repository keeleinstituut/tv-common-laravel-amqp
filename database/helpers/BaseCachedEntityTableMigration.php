<?php

namespace SyncTools\Database\Helpers;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class BaseCachedEntityTableMigration extends Migration
{
    public function getConnection(): ?string
    {
        return Config::get('pgsql-connection.sync.name', $this->connection);
    }
}
