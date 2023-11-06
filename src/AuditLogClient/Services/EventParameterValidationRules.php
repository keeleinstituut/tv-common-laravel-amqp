<?php

namespace AuditLogClient\Services;

use AuditLogClient\Enums\AuditLogEventObjectType;
use Illuminate\Validation\Rule;

class EventParameterValidationRules
{
    public static function buildRemoveObjectRules(string $fieldNamePrefix, ?AuditLogEventObjectType $objectType): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:object_type,object_identity_subset'],
            "$fieldNamePrefix.object_type" => ['required', Rule::enum(AuditLogEventObjectType::class)],
            ...static::buildIdentitySubsetRules("$fieldNamePrefix.object_identity_subset", $objectType),
        ];
    }

    public static function buildCreateObjectRules(string $fieldNamePrefix, ?AuditLogEventObjectType $objectType): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:object_type,object_data'],
            "$fieldNamePrefix.object_type" => ['required', Rule::enum(AuditLogEventObjectType::class)],
            "$fieldNamePrefix.object_data" => ['required', 'array'],
        ];
    }

    public static function buildModifyObjectRules(string $fieldNamePrefix, ?AuditLogEventObjectType $objectType): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:object_type,object_identity_subset,pre_modification_subset,post_modification_subset'],
            "$fieldNamePrefix.object_type" => ['required', Rule::enum(AuditLogEventObjectType::class)],
            "$fieldNamePrefix.pre_modification_subset" => ['required', 'array'],
            "$fieldNamePrefix.post_modification_subset" => ['required', 'array'],
            ...static::buildIdentitySubsetRules("$fieldNamePrefix.object_identity_subset", $objectType),
        ];
    }

    public static function buildEventParameterIsNullRule(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['present', Rule::in(null)],
        ];
    }

    public static function buildAnyEventParametersRule(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['present', 'nullable', 'array'],
        ];
    }

    public static function buildAssignmentRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:assignment_id,assignment_ext_id'],
            "$fieldNamePrefix.assignment_id" => 'required',
            "$fieldNamePrefix.assignment_ext_id" => 'required',
        ];
    }

    public static function buildProjectRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:project_id,project_ext_id'],
            "$fieldNamePrefix.project_id" => 'required',
            "$fieldNamePrefix.project_ext_id" => 'required',
        ];
    }

    public static function buildWorkflowReferenceRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:workflow_id,workflow_name'],
            "$fieldNamePrefix.workflow_id" => 'required',
            "$fieldNamePrefix.workflow_name" => 'required', // TODO: Confirm?
        ];
    }

    public static function buildNotificationDescriptionRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array'],
        ];
    }

    public static function buildProjectFileRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:media_id,project_id,project_ext_id,file_name'],
            "$fieldNamePrefix.media_id" => 'required',
            "$fieldNamePrefix.project_id" => 'required',
            "$fieldNamePrefix.project_ext_id" => 'required',
            "$fieldNamePrefix.file_name" => 'required', // TODO: Required?
        ];
    }

    public static function buildProjectExportRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:query_start_date,query_end_date,query_status'],
            "$fieldNamePrefix.query_start_date" => ['present', 'nullable', 'date', 'before_or_equal:end_date'],
            "$fieldNamePrefix.query_end_date" => ['present', 'nullable', 'date', 'after_or_equal:start_date'],
            "$fieldNamePrefix.query_status" => ['present', 'nullable', 'string'],
        ];
    }

    public static function buildTranslationMemoryRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:translation_memory_id,translation_memory_name'],
            "$fieldNamePrefix.translation_memory_id" => 'required',
            "$fieldNamePrefix.translation_memory_name" => 'required', // TODO: Confirm?
        ];
    }

    public static function buildAuditLogsRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix" => ['required', 'array:query_start_datetime,query_end_datetime,query_event_type,query_text,query_department_id'],
            "$fieldNamePrefix.query_start_datetime" => ['present', 'nullable', 'date'],
            "$fieldNamePrefix.query_end_datetime" => ['present', 'nullable', 'date'],
            "$fieldNamePrefix.query_event_type" => ['present', 'nullable', 'string'],
            "$fieldNamePrefix.query_text" => ['present', 'nullable', 'string'],
            "$fieldNamePrefix.query_department_id" => ['present', 'nullable', 'string'],
        ];
    }

    private static function buildIdentitySubsetRules(string $fieldNamePrefix, ?AuditLogEventObjectType $objectType): array
    {
        return match ($objectType) {
            AuditLogEventObjectType::InstitutionUser => [
                "$fieldNamePrefix" => ['required', 'array:id,user'],
                "$fieldNamePrefix.id" => ['required', 'uuid'],
                "$fieldNamePrefix.user" => ['required', 'array:id,personal_identification_code,forename,surname'],
                "$fieldNamePrefix.user.id" => ['required', 'uuid'],
                "$fieldNamePrefix.user.personal_identification_code" => ['filled', 'string'],
                "$fieldNamePrefix.user.forename" => ['filled', 'string'],
                "$fieldNamePrefix.user.surname" => ['filled', 'string'],
            ],
            AuditLogEventObjectType::Role,
            AuditLogEventObjectType::Institution,
            AuditLogEventObjectType::TranslationMemory => [
                "$fieldNamePrefix" => ['required', 'array:id,name'],
                "$fieldNamePrefix.id" => ['required', 'uuid'],
                "$fieldNamePrefix.name" => ['filled', 'string'],
            ],
            AuditLogEventObjectType::Vendor => [
                "$fieldNamePrefix" => ['required', 'array:id,institution_user'],
                "$fieldNamePrefix.id" => ['required', 'uuid'],
                "$fieldNamePrefix.institution_user" => ['required', 'array:id,user'],
                "$fieldNamePrefix.institution_user.id" => ['required', 'uuid'],
                "$fieldNamePrefix.institution_user.user" => ['required', 'array:id,personal_identification_code,forename,surname'],
                "$fieldNamePrefix.institution_user.user.id" => ['required', 'uuid'],
                "$fieldNamePrefix.institution_user.user.personal_identification_code" => ['filled', 'string'],
                "$fieldNamePrefix.institution_user.user.forename" => ['filled', 'string'],
                "$fieldNamePrefix.institution_user.user.surname" => ['filled', 'string'],
            ],
            AuditLogEventObjectType::Project,
            AuditLogEventObjectType::Subproject,
            AuditLogEventObjectType::Assignment => [
                "$fieldNamePrefix" => ['required', 'array:id,ext_id'],
                "$fieldNamePrefix.id" => ['required', 'uuid'],
                "$fieldNamePrefix.ext_id" => ['filled', 'string'],
            ],
            AuditLogEventObjectType::InstitutionDiscount => [
                "$fieldNamePrefix" => ['required', 'array:id,institution'],
                "$fieldNamePrefix.id" => ['required', 'uuid'],
                "$fieldNamePrefix.institution" => ['required', 'array:id,name'],
                "$fieldNamePrefix.institution.id" => ['required', 'uuid'],
                "$fieldNamePrefix.institution.name" => ['filled', 'string'],
            ],
            default => []
        };
    }
}
