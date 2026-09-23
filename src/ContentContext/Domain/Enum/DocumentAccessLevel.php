<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

enum DocumentAccessLevel: string
{
    case PUBLIC = 'PUBLIC';
    case MEMBER = 'MEMBER';
    case LAWYER = 'LAWYER';
    case RESTRICTED = 'RESTRICTED';
    case PRIVATE = 'PRIVATE';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Public',
            self::MEMBER => 'Membre connecté',
            self::LAWYER => 'Réservé aux avocats',
            self::RESTRICTED => 'Restreint',
            self::PRIVATE => 'Privé Backoffice',
        };
    }
}
