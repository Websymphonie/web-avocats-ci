<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum EnrollmentStatus: string
{
    case ACTIVE = 'ACTIVE';
    case REVOKED = 'REVOKED';

    public function label(): string
    {
        return match ($this) { self::ACTIVE => 'Actif', self::REVOKED => 'Révoqué' };
    }
}
