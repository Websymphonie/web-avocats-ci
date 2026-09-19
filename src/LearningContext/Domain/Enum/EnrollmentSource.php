<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum EnrollmentSource: string
{
    case SELF_SERVICE = 'SELF_SERVICE';
    case ADMIN_GRANT = 'ADMIN_GRANT';

    public function label(): string
    {
        return match ($this) { self::SELF_SERVICE => 'Auto-inscription', self::ADMIN_GRANT => 'Accès administrateur' };
    }
}
