<?php

namespace AuditLogClient\Enums;

enum AuditLogEventObjectType: string
{
    case InstitutionUser = 'INSTITUTION_USER';
    case Role = 'ROLE';
    case Institution = 'INSTITUTION';
    case Vendor = 'VENDOR';
    case InstitutionDiscount = 'INSTITUTION_DISCOUNT';
    case Project = 'PROJECT';
    case Subproject = 'SUBPROJECT';
    case Assignment = 'ASSIGNMENT';
    case TranslationMemory = 'TRANSLATION_MEMORY';

    public static function values(): array
    {
        return array_column(AuditLogEventObjectType::cases(), 'value');
    }
}
