<?php

namespace Amqp\Repositories;


use Carbon\Carbon;

interface CachedEntityRepositoryInterface
{
    public function save(array $resource): void;

    public function delete(string $id): void;

    public function getLastSyncDateTime(): string;

    public function deleteNotSynced(Carbon $syncStartTime): void;
}
