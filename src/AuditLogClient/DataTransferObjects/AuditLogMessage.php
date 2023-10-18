<?php

namespace AuditLogClient\DataTransferObjects;

use AuditLogClient\Enums\AuditLogEventFailureType;
use AuditLogClient\Enums\AuditLogEventType;
use DateTime;

readonly class AuditLogMessage
{
    public function __construct(
        public AuditLogEventType $eventType,
        public ?array $eventParameters,
        public DateTime $happenedAt,
        public string $traceId,
        public string $actingUserPic,
        public string $actingUserForename,
        public string $actingUserSurname,
        public ?AuditLogEventFailureType $failureType,
        public ?string $contextInstitutionId,
        public ?string $actingInstitutionUserId,
        public ?string $contextDepartmentId
    ) {
    }

    public function toArray(): array
    {
        return [
            'happened_at' => $this->happenedAt,
            'trace_id' => $this->traceId,
            'acting_user_pic' => $this->actingUserPic,
            'acting_user_forename' => $this->actingUserForename,
            'acting_user_surname' => $this->actingUserSurname,
            'event_type' => $this->eventType,
            'failure_type' => $this->failureType,
            'context_institution_id' => $this->contextInstitutionId,
            'acting_institution_user_id' => $this->actingInstitutionUserId,
            'context_department_id' => $this->contextDepartmentId,
            'event_parameters' => $this->eventParameters,
        ];
    }
}