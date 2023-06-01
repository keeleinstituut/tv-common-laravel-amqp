<?php

namespace SyncTools\Console\Base;

use Carbon\Carbon;
use Illuminate\Console\Command;
use SyncTools\Exceptions\ResourceGatewayConnectionException;
use SyncTools\Gateways\ResourceGatewayInterface;
use SyncTools\Repositories\CachedEntityRepositoryInterface;

abstract class BaseEntityFullSyncCommand extends Command
{
    /**
     * @throws ResourceGatewayConnectionException
     */
    public function handle(): void
    {
        $entityRepository = $this->getEntityRepository();

        $lastSyncDateTime = $this->prepareLastSyncDateTime();
        foreach ($this->getResourceGateway()->getResources() as $resource) {
            $this->getEntityRepository()->save($resource);
        }

        if (filled($lastSyncDateTime)) {
            $entityRepository->deleteNotSynced($lastSyncDateTime);
        }
    }

    abstract protected function getResourceGateway(): ResourceGatewayInterface;

    abstract protected function getEntityRepository(): CachedEntityRepositoryInterface;

    protected function prepareLastSyncDateTime(): ?Carbon
    {
        if (empty($lastSyncDateTimeAsString = $this->getEntityRepository()->getLastSyncDateTime())) {
            return null;
        }

        $lastSyncDateTime = Carbon::parse($lastSyncDateTimeAsString);
        if ($lastSyncDateTime === Carbon::now()->setTimezone($lastSyncDateTime->getTimezone())) {
            sleep(1);
        }

        return $lastSyncDateTime;
    }
}
