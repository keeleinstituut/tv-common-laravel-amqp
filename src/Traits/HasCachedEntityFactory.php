<?php

namespace SyncTools\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HasCachedEntityFactory
{
    use HasFactory {
        HasFactory::factory as protected getFactory;
    }

    /**
     * Get a new factory instance for the readonly model.
     *
     * @param callable|array|int|null $count
     * @param callable|array $state
     * @return Factory<static>
     */
    public static function factory($count = null, $state = []): Factory
    {
        return self::getFactory($count, $state)->connection(
            config('pgsql-connection.sync.name')
        );
    }
}