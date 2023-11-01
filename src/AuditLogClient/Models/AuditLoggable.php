<?php

namespace AuditLogClient\Models;

use AuditLogClient\Enums\AuditLogEventObjectType;

interface AuditLoggable
{
    public function getIdentitySubset(): array;

    public function getAuditLogRepresentation(): array;

    public function getAuditLogObjectType(): AuditLogEventObjectType;
}
