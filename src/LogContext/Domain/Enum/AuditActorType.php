<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Enum;

enum AuditActorType: string
{
    case USER = 'USER';
    case SYSTEM = 'SYSTEM';
}
