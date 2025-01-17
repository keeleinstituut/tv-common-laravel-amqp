<?php

namespace AuditLogClient\Enums;

enum AuditLogEventType: string
{
    case LogIn = 'LOG_IN';
    case LogOut = 'LOG_OUT';
    case SelectInstitution = 'SELECT_INSTITUTION';
    case CreateObject = 'CREATE_OBJECT';
    case ModifyObject = 'MODIFY_OBJECT';
    case RemoveObject = 'REMOVE_OBJECT';
    case CompleteAssignment = 'COMPLETE_ASSIGNMENT';
    case ApproveProject = 'APPROVE_PROJECT';
    case RejectProject = 'REJECT_PROJECT';
    case CancelProject = 'CANCEL_PROJECT';
    case ApproveAssignmentResult = 'APPROVE_ASSIGNMENT_RESULT';
    case RejectAssignmentResult = 'REJECT_ASSIGNMENT_RESULT';
    case RewindWorkflow = 'REWIND_WORKFLOW';
    case DispatchNotification = 'DISPATCH_NOTIFICATION';
    case DownloadProjectFile = 'DOWNLOAD_PROJECT_FILE';
    case DownloadSubProjectXliffs = 'DOWNLOAD_SUBPROJECT_CAT_XLIFF';
    case DownloadSubProjectTranslations = 'DOWNLOAD_SUBPROJECT_TRANSLATIONS';
    case ExportInstitutionUsers = 'EXPORT_INSTITUTION_USERS';
    case ExportProjectsReport = 'EXPORT_PROJECTS_REPORT';
    case ExportTranslationMemory = 'EXPORT_TRANSLATION_MEMORY';
    case ImportTranslationMemory = 'IMPORT_TRANSLATION_MEMORY';
    case SearchLogs = 'SEARCH_LOGS';
    case ExportLogs = 'EXPORT_LOGS';
    case AcceptTask = 'ACCEPT_TASK';

    public static function values(): array
    {
        return array_column(AuditLogEventType::cases(), 'value');
    }
}
