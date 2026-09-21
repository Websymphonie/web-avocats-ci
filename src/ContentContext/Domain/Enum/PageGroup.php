<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

enum PageGroup: string
{
    case LEGAL = 'LEGAL';
    case BAR = 'BAR';
    case ACCOUNT = 'ACCOUNT';

    public function label(): string
    {
        return match ($this) {
            self::LEGAL => 'Informations légales',
            self::BAR => 'Le Barreau',
            self::ACCOUNT => 'Compte et confidentialité',
        };
    }
}
