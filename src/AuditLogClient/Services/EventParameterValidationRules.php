<?php

namespace AuditLogClient\Services;

use AuditLogClient\Enums\AuditLogEventObjectType;
use Illuminate\Validation\Rule;

class EventParameterValidationRules
{
    public static function buildRemoveObjectRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.object_type" => ['required', Rule::enum(AuditLogEventObjectType::class)],
            "$fieldNamePrefix.object_identity_subset" => ['required', 'array'],
        ];
    }

    public static function buildCreateObjectRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.object_type" => ['required', Rule::enum(AuditLogEventObjectType::class)],
            "$fieldNamePrefix.object_data" => ['required', 'array'],
        ];
    }

    public static function buildModifyObjectRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.object_type" => ['required', Rule::enum(AuditLogEventObjectType::class)],
            "$fieldNamePrefix.object_id" => 'required',
            "$fieldNamePrefix.pre_modification_subset" => ['required', 'array'],
            "$fieldNamePrefix.post_modification_subset" => ['required', 'array'],
        ];
    }

    public static function buildNoParametersRules(): array
    {
        return []; // event type expects no parameters
    }

    public static function buildAssignmentRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.assignment_id" => 'required',
            "$fieldNamePrefix.assignment_ext_id" => 'required',
        ];
    }

    public static function buildProjectRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.project_id" => 'required',
            "$fieldNamePrefix.project_ext_id" => 'required',
        ];
    }

    public static function buildWorkflowReferenceRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.workflow_id" => 'required',
            "$fieldNamePrefix.workflow_name" => 'required', // TODO: Confirm?
        ];
    }

    public static function buildNotificationDescriptionRules(string $fieldNamePrefix): array
    {
        return [
            // TODO
        ];
    }

    public static function buildProjectFileRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.media_id" => 'required',
            "$fieldNamePrefix.project_id" => 'required',
            "$fieldNamePrefix.file_name" => 'required', // TODO: Required?
        ];
    }

    public static function buildProjectExportRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.query_start_date" => ['present', 'nullable', 'date', 'before_or_equal:end_date'],
            "$fieldNamePrefix.query_end_date" => ['present', 'nullable', 'date', 'after_or_equal:start_date'],
            "$fieldNamePrefix.query_status" => ['present', 'nullable', 'string'],
        ];
    }

    public static function buildTranslationMemoryRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.translation_memory_id" => 'required',
            "$fieldNamePrefix.translation_memory_name" => 'required', // TODO: Confirm?
        ];
    }

    public static function buildAuditLogsRules(string $fieldNamePrefix): array
    {
        return [
            "$fieldNamePrefix.query_start_datetime" => ['present', 'nullable', 'date'],
            "$fieldNamePrefix.query_end_datetime" => ['present', 'nullable', 'date'],
            "$fieldNamePrefix.query_event_type" => ['present', 'nullable', 'string'],
            "$fieldNamePrefix.query_text" => ['present', 'nullable', 'string'],
            "$fieldNamePrefix.query_department_id" => ['present', 'nullable', 'string'],
        ];
    }
}
