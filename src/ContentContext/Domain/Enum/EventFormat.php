<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

enum EventFormat: string
{
    case IN_PERSON = 'IN_PERSON';
    case ONLINE = 'ONLINE';
    case HYBRID = 'HYBRID';

    public function label(): string
    {
        return match ($this) {
            self::IN_PERSON => 'Présentiel',
            self::ONLINE => 'En ligne',
            self::HYBRID => 'Hybride',
        };
    }
}
