<?php

namespace AuditLogClient\Services;

use AuditLogClient\DataTransferObjects\AuditLogMessage;
use AuditLogClient\Enums\AuditLogEventType;
use AuditLogClient\Models\AuditLoggable;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use KeycloakAuthGuard\Services\ServiceAccountJwtRetrieverInterface;
use SyncTools\AmqpPublisher;
use Throwable;

readonly class AuditLogPublisher
{
    public function __construct(
        private AmqpPublisher $publisher,
        private AuditLogMessageValidationService $validationService,
        private ServiceAccountJwtRetrieverInterface $jwtRetriever
    ) {
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function publish(AuditLogMessage $auditLogEvent): void
    {
        if (empty($auditLogEvent->failureType) && static::isEmptyModifyObjectEvent($auditLogEvent)) {
            return;
        }

        $validator = $this->validationService->makeValidator($auditLogEvent->toArray());
        $validator->validate();

        $exchange = Config::get('amqp.audit_logs.exchange');
        throw_if(empty($exchange), 'Exchange name has not been declared.');

        $jwt = $this->jwtRetriever->getJwt();

        $this->publisher->publish(
            $validator->validated(),
            $exchange,
            headers: ['jwt' => $jwt]
        );
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function publishRemoveObject(AuditLoggable $object): void
    {
        $message = AuditLogMessageBuilder::makeUsingJWT()->toRemoveObjectEvent(
            $object->getAuditLogObjectType(),
            $object->getIdentitySubset()
        );

        $this->publish($message);
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function publishCreateObject(AuditLoggable $object): void
    {
        $message = AuditLogMessageBuilder::makeUsingJWT()->toCreateObjectEvent(
            $object->getAuditLogObjectType(),
            $object->getAuditLogRepresentation()
        );

        $this->publish($message);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $action
     * @return T
     *
     * @throws ValidationException
     * @throws Throwable
     */
    public function publishModifyObjectAfterAction(AuditLoggable $object, Closure $action): mixed
    {
        $identityBeforeAction = $object->getIdentitySubset();
        $dataBeforeAction = $object->getAuditLogRepresentation();

        $result = $action();
        $dataAfterAction = $object->getAuditLogRepresentation();

        $message = AuditLogMessageBuilder::makeUsingJWT()->toModifyObjectEventComputingDiff(
            $object->getAuditLogObjectType(),
            $identityBeforeAction,
            $dataBeforeAction,
            $dataAfterAction
        );

        $this->publish($message);

        return $result;
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $action
     * @param  array<AuditLoggable>|Collection<AuditLoggable>  $objects
     * @return T
     *
     * @throws Throwable
     */
    public function publishModifyObjectsAfterAction(array|Collection $objects, Closure $action): mixed
    {

        $objectsIdentityAndDataBeforeAction = collect($objects)
            ->map(fn (AuditLoggable $object) => [
                $object,
                $object->getIdentitySubset(),
                $object->getAuditLogRepresentation(),
            ]);

        $result = $action();

        $objectsIdentityAndDataBeforeAction->eachSpread(
            function (AuditLoggable $object, array $identityBeforeAction, array $dataBeforeAction): void {
                $dataAfterAction = $object->getAuditLogRepresentation();

                $message = AuditLogMessageBuilder::makeUsingJWT()->toModifyObjectEventComputingDiff(
                    $object->getAuditLogObjectType(),
                    $identityBeforeAction,
                    $dataBeforeAction,
                    $dataAfterAction
                );

                $this->publish($message);
            }
        );

        return $result;
    }

    private static function isEmptyModifyObjectEvent(AuditLogMessage $auditLogEvent): bool
    {
        return $auditLogEvent->eventType === AuditLogEventType::ModifyObject
            && $auditLogEvent->eventParameters['pre_modification_subset'] === []
            && $auditLogEvent->eventParameters['post_modification_subset'] === [];
    }
}
