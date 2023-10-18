<?php

namespace AuditLogClient\Services;

use AuditLogClient\Enums\AuditLogEventFailureType;
use AuditLogClient\Enums\AuditLogEventObjectType;
use AuditLogClient\Enums\AuditLogEventType;
use BadMethodCallException;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Request;
use AuditLogClient\DataTransferObjects\AuditLogEvent;

class AuditLogEventBuilder
{
    public function __construct(
        private ?DateTime                 $happenedAt = null,
        private ?string                   $traceId = null,
        private ?string                   $actingUserPic = null,
        private ?string                   $actingUserForename = null,
        private ?string                   $actingUserSurname = null,
        private ?AuditLogEventFailureType $failureType = null,
        private ?string                   $contextInstitutionId = null,
        private ?string                   $actingInstitutionUserId = null,
        private ?string                   $contextDepartmentId = null
    )
    {
        if (empty($happenedAt)) {
            $this->happenedAt = Date::now();
        }
    }

    public static function make(
        ?DateTime                 $happenedAt = null,
        ?string                   $traceId = null,
        ?string                   $actingUserPic = null,
        ?string                   $actingUserForename = null,
        ?string                   $actingUserSurname = null,
        ?AuditLogEventFailureType $failureType = null,
        ?string                   $contextInstitutionId = null,
        ?string                   $actingInstitutionUserId = null,
        ?string                   $contextDepartmentId = null
    ): static
    {
        return new static(
            $happenedAt,
            $traceId,
            $actingUserPic,
            $actingUserForename,
            $actingUserSurname,
            $failureType,
            $contextInstitutionId,
            $actingInstitutionUserId,
            $contextDepartmentId
        );
    }

    public static function makeUsingJWT(?AuditLogEventFailureType $failureType = null): static
    {
        return new static(
            Date::now(),
            static::retrieveCurrentTraceId(),
            static::retrieveCurrentUserPic(),
            static::retrieveCurrentUserForename(),
            static::retrieveCurrentUserSurname(),
            $failureType,
            static::retrieveCurrentInstitutionId(),
            static::retrieveCurrentInstitutionUserId(),
            static::retrieveCurrentDepartmentId(),
        );
    }

    public function toFinishProjectEvent(string $projectId, string $projectExtId): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::FinishProject, [
            'project_id' => $projectId,
            'project_ext_id' => $projectExtId
        ]);
    }

    public function toRewindWorkflowEvent(string $workflowId, string $workflowName): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::RewindWorkflow, [
            'workflow_id' => $workflowId,
            'workflow_name' => $workflowName
        ]);
    }

    /** @todo */
    public function toDispatchNotificationEvent()
    {
        throw new BadMethodCallException('DISPATCH_NOTIFICATION payload not yet determined or implemented.');
    }

    public function toDownloadProjectFileEvent(mixed $mediaId, string $projectId, string $fileName): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::DownloadProjectFile, [
            'media_id' => $mediaId,
            'project_id' => $projectId,
            'file_name' => $fileName
        ]);
    }

    public function toExportProjectsReportEvent(?string $queryStartDate, ?string $queryEndDate, ?string $queryStatus): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::ExportProjectsReport, [
            'query_start_date' => $queryStartDate,
            'query_end_date' => $queryEndDate,
            'query_status' => $queryStatus
        ]);
    }

    public function toModifyObjectEvent(AuditLogEventObjectType $objectType, mixed $objectId, array $preModificationSubset, array $postModificationSubset): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::ModifyObject, [
            'object_type' => $objectType,
            'object_id' => $objectId,
            'pre_modification_subset' => $preModificationSubset,
            'post_modification_subset' => $postModificationSubset
        ]);
    }

    public function toCreateObjectEvent(AuditLogEventObjectType $objectType, array $objectData): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::CreateObject, [
            "object_type" => $objectType,
            "object_data" => $objectData
        ]);
    }

    public function toRemoveObjectEvent(AuditLogEventObjectType $objectType, array $objectIdentitySubset): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::RemoveObject, [
            "object_type" => $objectType,
            "object_identity_subset" => $objectIdentitySubset,
        ]);
    }

    public function toImportTranslationMemoryEvent(mixed $translationMemoryId, string $translationMemoryName): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::ImportTranslationMemory, [
            'translation_memory_id' => $translationMemoryId,
            'translation_memory_name' => $translationMemoryName, // TODO: Confirm?
        ]);
    }

    public function toExportTranslationMemoryEvent(mixed $translationMemoryId, string $translationMemoryName): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::ExportTranslationMemory, [
            'translation_memory_id' => $translationMemoryId,
            'translation_memory_name' => $translationMemoryName, // TODO: Confirm?
        ]);
    }

    public function toSearchLogsEvent(?string $queryStartDatetime, ?string $queryEndDatetime, ?string $queryEventType, ?string $queryDepartmentId, ?string $queryText): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::SearchLogs, [
            "query_start_datetime" => $queryStartDatetime,
            "query_end_datetime" => $queryEndDatetime,
            "query_event_type" => $queryEventType,
            "query_department_id" => $queryDepartmentId,
            "query_text" => $queryText,
        ]);
    }

    public function toExportLogsEvent(?string $queryStartDatetime, ?string $queryEndDatetime, ?string $queryEventType, ?string $queryDepartmentId, ?string $queryText): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::ExportLogs, [
            "query_start_datetime" => $queryStartDatetime,
            "query_end_datetime" => $queryEndDatetime,
            "query_event_type" => $queryEventType,
            "query_department_id" => $queryDepartmentId,
            "query_text" => $queryText,
        ]);
    }

    public function toRejectAssignmentResultEvent(string $assignmentId, string $assignmentExtId): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::RejectAssignmentResult, [
            'assignment_id' => $assignmentId,
            'assignment_ext_id' => $assignmentExtId,
        ]);
    }

    public function toApproveAssignmentResultEvent(string $assignmentId, string $assignmentExtId): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::ApproveAssignmentResult, [
            'assignment_id' => $assignmentId,
            'assignment_ext_id' => $assignmentExtId,
        ]);
    }

    public function toCompleteAssignmentEvent(string $assignmentId, string $assignmentExtId): AuditLogEvent
    {
        return $this->toMessageEvent(AuditLogEventType::CompleteAssignment, [
            'assignment_id' => $assignmentId,
            'assignment_ext_id' => $assignmentExtId,
        ]);
    }


    public function toMessageEvent(AuditLogEventType $eventType, ?array $eventParameters): AuditLogEvent
    {
        return new AuditLogEvent(
            $eventType,
            $eventParameters,
            $this->happenedAt,
            $this->traceId,
            $this->actingUserPic,
            $this->actingUserForename,
            $this->actingUserSurname,
            $this->failureType,
            $this->contextInstitutionId,
            $this->actingInstitutionUserId,
            $this->contextDepartmentId
        );
    }


    public function traceId(?string $traceId): AuditLogEventBuilder
    {
        $this->traceId = $traceId;
        return $this;
    }

    public function actingUserPic(?string $actingUserPic): AuditLogEventBuilder
    {
        $this->actingUserPic = $actingUserPic;
        return $this;
    }

    public function actingUserForename(?string $actingUserForename): AuditLogEventBuilder
    {
        $this->actingUserForename = $actingUserForename;
        return $this;
    }

    public function actingUserSurname(?string $actingUserSurname): AuditLogEventBuilder
    {
        $this->actingUserSurname = $actingUserSurname;
        return $this;
    }

    public function failureType(?AuditLogEventFailureType $failureType): AuditLogEventBuilder
    {
        $this->failureType = $failureType;
        return $this;
    }

    public function contextInstitutionId(?string $contextInstitutionId): AuditLogEventBuilder
    {
        $this->contextInstitutionId = $contextInstitutionId;
        return $this;
    }

    public function actingInstitutionUserId(?string $actingInstitutionUserId): AuditLogEventBuilder
    {
        $this->actingInstitutionUserId = $actingInstitutionUserId;
        return $this;
    }

    public function contextDepartmentId(?string $contextDepartmentId): AuditLogEventBuilder
    {
        $this->contextDepartmentId = $contextDepartmentId;
        return $this;
    }


    private static function retrieveCurrentTraceId(): string
    {
        return Request::header('X-Trace-Id');
    }

    private static function retrieveCurrentUserPic(): string
    {
        return Auth::getCustomClaimsTokenData('personalIdentificationCode');
    }

    private static function retrieveCurrentUserForename(): string
    {
        return Auth::getCustomClaimsTokenData('forename');
    }

    private static function retrieveCurrentUserSurname(): string
    {
        return Auth::getCustomClaimsTokenData('surname');
    }

    private static function retrieveCurrentInstitutionId(): string
    {
        return Auth::getCustomClaimsTokenData('selectedInstitution.id');
    }

    private static function retrieveCurrentInstitutionUserId(): string
    {
        return Auth::getCustomClaimsTokenData('institutionUserId');
    }

    private static function retrieveCurrentDepartmentId(): string
    {
        return Auth::getCustomClaimsTokenData('departmentId');
    }
}
