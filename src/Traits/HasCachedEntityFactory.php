<?php

namespace Amqp\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;

trait HasCachedEntityFactory
{
    use HasFactory {
        factory as protected getFactory;
    }

    /**
     * Get a new factory instance for the readonly model.
     *
     * @param  callable|array|int|null  $count
     * @param  callable|array  $state
     * @return Factory<static>
     */
    public static function factory($count = null, $state = []): Factory
    {
        return self::getFactory($count, $state)->connection(Config::get('sync.pgsql_sync_connection.name'));
    }
}