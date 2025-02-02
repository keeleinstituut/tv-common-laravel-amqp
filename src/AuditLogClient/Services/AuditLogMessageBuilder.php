<?php

namespace AuditLogClient\Services;

use AuditLogClient\DataTransferObjects\AuditLogMessage;
use AuditLogClient\Enums\AuditLogEventFailureType;
use AuditLogClient\Enums\AuditLogEventObjectType;
use AuditLogClient\Enums\AuditLogEventType;
use AuditLogClient\Util\ArrayUtil;
use BadMethodCallException;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Request;

class AuditLogMessageBuilder
{
    public function __construct(
        private ?CarbonInterface $happenedAt = null,
        private ?string $traceId = null,
        private ?string $actingUserPic = null,
        private ?string $actingUserForename = null,
        private ?string $actingUserSurname = null,
        private ?AuditLogEventFailureType $failureType = null,
        private ?string $contextInstitutionId = null,
        private ?string $actingInstitutionUserId = null,
        private ?string $contextDepartmentId = null
    ) {
        if (empty($happenedAt)) {
            $this->happenedAt = Date::now();
        }
    }

    public static function make(
        ?CarbonInterface $happenedAt = null,
        ?string $traceId = null,
        ?string $actingUserPic = null,
        ?string $actingUserForename = null,
        ?string $actingUserSurname = null,
        ?AuditLogEventFailureType $failureType = null,
        ?string $contextInstitutionId = null,
        ?string $actingInstitutionUserId = null,
        ?string $contextDepartmentId = null
    ): static {
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

    public function toApproveProjectEvent(string $projectId, string $projectExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ApproveProject, [
            'project_id' => $projectId,
            'project_ext_id' => $projectExtId,
        ]);
    }

    public function toRejectProjectEvent(string $projectId, string $projectExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::RejectProject, [
            'project_id' => $projectId,
            'project_ext_id' => $projectExtId,
        ]);
    }

    public function toCancelProjectEvent(string $projectId, string $projectExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::CancelProject, [
            'project_id' => $projectId,
            'project_ext_id' => $projectExtId,
        ]);
    }

    public function toRewindWorkflowEvent(string $workflowId, string $workflowName): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::RewindWorkflow, [
            'workflow_id' => $workflowId,
            'workflow_name' => $workflowName,
        ]);
    }

    public function toDispatchNotificationEvent($eventData)
    {
        return $this->toMessageEvent(AuditLogEventType::DispatchNotification, $eventData);
    }

    public function toDownloadProjectFileEvent(mixed $mediaId, string $projectId, string $projectExtId, string $fileName): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::DownloadProjectFile, [
            'media_id' => $mediaId,
            'project_id' => $projectId,
            'project_ext_id' => $projectExtId,
            'file_name' => $fileName,
        ]);
    }

    public function toDownloadSubprojectXliffsEvent(string $subProjectId, string $subProjectExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::DownloadSubProjectXliffs, [
            'subproject_id' => $subProjectId,
            'subproject_ext_id' => $subProjectExtId,
        ]);
    }

    public function toDownloadSubprojectTranslationsEvent(string $subProjectId, string $subProjectExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::DownloadSubProjectTranslations, [
            'subproject_id' => $subProjectId,
            'subproject_ext_id' => $subProjectExtId,
        ]);
    }

    public function toExportProjectsReportEvent(?string $queryStartDate, ?string $queryEndDate, ?array $queryStatus): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ExportProjectsReport, [
            'query_start_date' => $queryStartDate,
            'query_end_date' => $queryEndDate,
            'query_status' => $queryStatus,
        ]);
    }

    public function toModifyObjectEvent(AuditLogEventObjectType $objectType, ?array $objectIdentitySubset, array $preModificationSubset, array $postModificationSubset): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ModifyObject, [
            'object_type' => $objectType->value,
            'object_identity_subset' => $objectIdentitySubset,
            'pre_modification_subset' => $preModificationSubset,
            'post_modification_subset' => $postModificationSubset,
        ]);
    }

    public function toModifyObjectEventComputingDiff(AuditLogEventObjectType $objectType, ?array $objectIdentitySubset, array $objectBeforeChanges, array $objectAfterChanges): AuditLogMessage
    {
        [$preModificationSubset, $postModificationSubset] = ArrayUtil::multiDiffPairs($objectBeforeChanges, $objectAfterChanges);

        return $this->toMessageEvent(AuditLogEventType::ModifyObject, [
            'object_type' => $objectType->value,
            'object_identity_subset' => $objectIdentitySubset,
            'pre_modification_subset' => $preModificationSubset,
            'post_modification_subset' => $postModificationSubset,
        ]);
    }

    public function toCreateObjectEvent(AuditLogEventObjectType $objectType, array $objectData, array $objectIdentitySubset): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::CreateObject, [
            'object_type' => $objectType->value,
            'object_data' => $objectData,
            'object_identity_subset' => $objectIdentitySubset,
        ]);
    }

    public function toRemoveObjectEvent(AuditLogEventObjectType $objectType, ?array $objectIdentitySubset): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::RemoveObject, [
            'object_type' => $objectType->value,
            'object_identity_subset' => $objectIdentitySubset,
        ]);
    }

    public function toImportTranslationMemoryEvent(mixed $translationMemoryId, string $translationMemoryName): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ImportTranslationMemory, [
            'translation_memory_id' => $translationMemoryId,
            'translation_memory_name' => $translationMemoryName, // TODO: Confirm?
        ]);
    }

    public function toExportTranslationMemoryEvent(mixed $translationMemoryId, string $translationMemoryName): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ExportTranslationMemory, [
            'translation_memory_id' => $translationMemoryId,
            'translation_memory_name' => $translationMemoryName, // TODO: Confirm?
        ]);
    }

    public function toSearchLogsEvent(?string $queryStartDatetime, ?string $queryEndDatetime, ?string $queryEventType, ?string $queryDepartmentId, ?string $queryText, ?string $queryActingUserPic): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::SearchLogs, [
            'query_start_datetime' => $queryStartDatetime,
            'query_end_datetime' => $queryEndDatetime,
            'query_event_type' => $queryEventType,
            'query_department_id' => $queryDepartmentId,
            'query_text' => $queryText,
            'acting_user_pic' => $queryActingUserPic
        ]);
    }

    public function toExportLogsEvent(?string $queryStartDatetime, ?string $queryEndDatetime, ?string $queryEventType, ?string $queryDepartmentId, ?string $queryText, ?string $queryActingUserPic): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ExportLogs, [
            'query_start_datetime' => $queryStartDatetime,
            'query_end_datetime' => $queryEndDatetime,
            'query_event_type' => $queryEventType,
            'query_department_id' => $queryDepartmentId,
            'query_text' => $queryText,
            'acting_user_pic' => $queryActingUserPic
        ]);
    }

    public function toRejectAssignmentResultEvent(string $assignmentId, string $assignmentExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::RejectAssignmentResult, [
            'assignment_id' => $assignmentId,
            'assignment_ext_id' => $assignmentExtId,
        ]);
    }

    public function toApproveAssignmentResultEvent(string $assignmentId, string $assignmentExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ApproveAssignmentResult, [
            'assignment_id' => $assignmentId,
            'assignment_ext_id' => $assignmentExtId,
        ]);
    }

    public function toCompleteAssignmentEvent(string $assignmentId, string $assignmentExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::CompleteAssignment, [
            'assignment_id' => $assignmentId,
            'assignment_ext_id' => $assignmentExtId,
        ]);
    }

    public function toAcceptTaskEvent(string $assignmentId, string $assignmentExtId): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::AcceptTask, [
            'assignment_id' => $assignmentId,
            'assignment_ext_id' => $assignmentExtId,
        ]);
    }

    public function toLogInEvent(): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::LogIn, null);
    }

    public function toLogOutEvent(): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::LogOut, null);
    }

    public function toSelectInstitutionEvent(): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::SelectInstitution, null);
    }

    public function toExportInstitutionUsers(): AuditLogMessage
    {
        return $this->toMessageEvent(AuditLogEventType::ExportInstitutionUsers, null);
    }

    public function toEventWithUnprocessableEntityFailure(AuditLogEventType $eventType, ?array $eventParameters): AuditLogMessage
    {
        return $this
            ->failureType(AuditLogEventFailureType::UNPROCESSABLE_ENTITY)
            ->toMessageEvent($eventType, $eventParameters);
    }

    public function toMessageEvent(AuditLogEventType $eventType, ?array $eventParameters): AuditLogMessage
    {
        return new AuditLogMessage(
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

    public function traceId(?string $traceId): AuditLogMessageBuilder
    {
        $this->traceId = $traceId;

        return $this;
    }

    public function actingUserPic(?string $actingUserPic): AuditLogMessageBuilder
    {
        $this->actingUserPic = $actingUserPic;

        return $this;
    }

    public function actingUserForename(?string $actingUserForename): AuditLogMessageBuilder
    {
        $this->actingUserForename = $actingUserForename;

        return $this;
    }

    public function actingUserSurname(?string $actingUserSurname): AuditLogMessageBuilder
    {
        $this->actingUserSurname = $actingUserSurname;

        return $this;
    }

    public function failureType(?AuditLogEventFailureType $failureType): AuditLogMessageBuilder
    {
        $this->failureType = $failureType;

        return $this;
    }

    public function contextInstitutionId(?string $contextInstitutionId): AuditLogMessageBuilder
    {
        $this->contextInstitutionId = $contextInstitutionId;

        return $this;
    }

    public function actingInstitutionUserId(?string $actingInstitutionUserId): AuditLogMessageBuilder
    {
        $this->actingInstitutionUserId = $actingInstitutionUserId;

        return $this;
    }

    public function contextDepartmentId(?string $contextDepartmentId): AuditLogMessageBuilder
    {
        $this->contextDepartmentId = $contextDepartmentId;

        return $this;
    }

    private static function retrieveCurrentTraceId(): ?string
    {
        $headerName = Config::get('amqp.audit_logs.trace_id_http_header');

        return Request::header($headerName);
    }

    private static function retrieveCurrentUserPic(): ?string
    {
        return static::getJwtTokenData('personalIdentificationCode');
    }

    private static function retrieveCurrentUserForename(): ?string
    {
        return static::getJwtTokenData('forename');
    }

    private static function retrieveCurrentUserSurname(): ?string
    {
        return static::getJwtTokenData('surname');
    }

    private static function retrieveCurrentInstitutionId(): ?string
    {
        return static::getJwtTokenData('selectedInstitution.id');
    }

    private static function retrieveCurrentInstitutionUserId(): ?string
    {
        return static::getJwtTokenData('institutionUserId');
    }

    private static function retrieveCurrentDepartmentId(): ?string
    {
        return static::getJwtTokenData('department.id');
    }

    private static function getJwtTokenData(string $key): ?string {
        if (!Auth::check()) {
            return "";
        }
        return Auth::getCustomClaimsTokenData($key);
    }
}
