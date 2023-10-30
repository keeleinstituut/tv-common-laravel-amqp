<?php

namespace AuditLogClient\Services;

use AuditLogClient\Enums\AuditLogEventFailureType;
use AuditLogClient\Enums\AuditLogEventObjectType;
use AuditLogClient\Enums\AuditLogEventType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuditLogMessageValidationService
{
    public function makeValidator(array $messageBody): \Illuminate\Contracts\Validation\Validator
    {
        $eventType = AuditLogEventType::tryFrom(Arr::get($messageBody, 'event_type'));
        $objectType = AuditLogEventObjectType::tryFrom(Arr::get($messageBody, 'event_parameters.object_type'));
        $failureType = AuditLogEventFailureType::tryFrom(Arr::get($messageBody, 'failure_type'));

        return Validator::make($messageBody, $this->rules($eventType, $failureType, $objectType));
    }

    public function rules(?AuditLogEventType $eventType, ?AuditLogEventFailureType $failureType, ?AuditLogEventObjectType $objectType): array
    {
        return [
            'happened_at' => ['required', 'date'],
            'trace_id' => ['present', 'nullable', 'string'],
            'event_type' => ['required', Rule::enum(AuditLogEventType::class)],
            'failure_type' => ['present', 'nullable', Rule::enum(AuditLogEventFailureType::class)],
            'context_institution_id' => ['present', 'nullable', 'uuid'],
            'acting_institution_user_id' => ['present', 'nullable', 'uuid'],
            'context_department_id' => ['present', 'nullable', 'uuid'],
            'acting_user_pic' => ['present', 'nullable', 'string'],
            'acting_user_forename' => ['present', 'nullable', 'string'],
            'acting_user_surname' => ['present', 'nullable', 'string'],
            ...static::buildEventParametersRules('event_parameters', $eventType, $failureType, $objectType),
        ];
    }

    public static function buildEventParametersRules(string $fieldNamePrefix, ?AuditLogEventType $eventType, ?AuditLogEventFailureType $failureType, ?AuditLogEventObjectType $objectType): array
    {
        if (filled($failureType)) {
            return EventParameterValidationRules::buildAnyEventParametersRule($fieldNamePrefix);
        }

        return match ($eventType) {
            AuditLogEventType::FinishProject => EventParameterValidationRules::buildProjectRules($fieldNamePrefix),
            AuditLogEventType::RewindWorkflow => EventParameterValidationRules::buildWorkflowReferenceRules($fieldNamePrefix),
            AuditLogEventType::DispatchNotification => EventParameterValidationRules::buildNotificationDescriptionRules($fieldNamePrefix),
            AuditLogEventType::DownloadProjectFile => EventParameterValidationRules::buildProjectFileRules($fieldNamePrefix),
            AuditLogEventType::ExportProjectsReport => EventParameterValidationRules::buildProjectExportRules($fieldNamePrefix),
            AuditLogEventType::ModifyObject => EventParameterValidationRules::buildModifyObjectRules($fieldNamePrefix, $objectType),
            AuditLogEventType::CreateObject => EventParameterValidationRules::buildCreateObjectRules($fieldNamePrefix, $objectType),
            AuditLogEventType::RemoveObject => EventParameterValidationRules::buildRemoveObjectRules($fieldNamePrefix, $objectType),
            AuditLogEventType::ImportTranslationMemory,
            AuditLogEventType::ExportTranslationMemory => EventParameterValidationRules::buildTranslationMemoryRules($fieldNamePrefix),
            AuditLogEventType::SearchLogs,
            AuditLogEventType::ExportLogs => EventParameterValidationRules::buildAuditLogsRules($fieldNamePrefix),
            AuditLogEventType::RejectAssignmentResult,
            AuditLogEventType::ApproveAssignmentResult,
            AuditLogEventType::CompleteAssignment => EventParameterValidationRules::buildAssignmentRules($fieldNamePrefix),
            AuditLogEventType::LogOut,
            AuditLogEventType::ExportInstitutionUsers,
            AuditLogEventType::SelectInstitution,
            AuditLogEventType::LogIn,
            null => EventParameterValidationRules::buildEventParameterIsNullRule($fieldNamePrefix), // event type expects no parameters
        };
    }
}
