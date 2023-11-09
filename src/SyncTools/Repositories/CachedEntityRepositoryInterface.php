<?php

namespace SyncTools\Repositories;

interface CachedEntityRepositoryInterface
{
    public function save(array $resource): void;

    public function delete(string $id): void;

    public function cleanupLastSyncDateTime(): void;

    public function deleteNotSynced(): void;
}
