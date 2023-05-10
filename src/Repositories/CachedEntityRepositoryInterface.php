<?php

namespace Amqp\Repositories;

use Illuminate\Support\Carbon;

interface CachedEntityRepositoryInterface
{
    public function save(array $resource): void;

    public function delete(string $id): void;

    public function deleteNotSynced(Carbon $syncStartTime): void;
}
