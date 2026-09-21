<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum EventFormat: string
{
    case IN_PERSON = 'IN_PERSON';
    case ONLINE = 'ONLINE';
    case HYBRID = 'HYBRID';

    public function badge(): string
    {
        return match ($this) {
            self::IN_PERSON => ColorEnum::PURPLE->value,
            self::ONLINE => ColorEnum::SUCCESS->value,
            self::HYBRID => ColorEnum::PINK->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::IN_PERSON => 'Présentiel',
            self::ONLINE => 'En ligne',
            self::HYBRID => 'Hybride',
        };
    }
}
