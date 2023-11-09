<?php

namespace SyncTools\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;

class MigrationCommandStartingListener
{
    public function handle(CommandStarting $event): void
    {
        if ($event->command === 'migrate:fresh') {
            Artisan::call('db:wipe', ['--database' => config('pgsql-connection.sync.name')]);
        }

        if ($event->command === 'migrate') {
            Artisan::call('db-schema:setup');
        }
    }
}
