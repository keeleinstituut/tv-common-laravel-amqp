<?php

namespace SyncTools\Console\Base;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SyncTools\Exceptions\ResourceGatewayConnectionException;
use SyncTools\Gateways\ResourceGatewayInterface;
use SyncTools\Repositories\CachedEntityRepositoryInterface;

abstract class BaseEntityFullSyncCommand extends Command
{
    protected int $transactionAttemptsCount = 5;

    /**
     * @throws ResourceGatewayConnectionException
     */
    public function handle(): void
    {
        $entityRepository = $this->getEntityRepository();
        $entityRepository->cleanupLastSyncDateTime();

        foreach ($this->getResourceGateway()->getResources() as $resource) {
            DB::transaction(
                fn () => $entityRepository->save($resource),
                $this->transactionAttemptsCount
            );
        }

        $entityRepository->deleteNotSynced();
    }

    abstract protected function getResourceGateway(): ResourceGatewayInterface;

    abstract protected function getEntityRepository(): CachedEntityRepositoryInterface;
}
