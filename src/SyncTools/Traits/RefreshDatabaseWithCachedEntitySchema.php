<?php

namespace SyncTools\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Artisan;

trait RefreshDatabaseWithCachedEntitySchema
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (! RefreshDatabaseState::$migrated) {
            Artisan::call('db:wipe', ['--database' => config('pgsql-connection.sync.name')]);
        }
    }
}
