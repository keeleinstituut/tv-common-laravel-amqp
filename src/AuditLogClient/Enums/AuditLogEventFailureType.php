<?php

namespace AuditLogClient\Enums;

enum AuditLogEventFailureType: string
{
    case UNPROCESSABLE_ENTITY = 'UNPROCESSABLE_ENTITY';
    case FORBIDDEN = 'FORBIDDEN';

    public static function values(): array
    {
        return array_column(AuditLogEventFailureType::cases(), 'value');
    }
}
