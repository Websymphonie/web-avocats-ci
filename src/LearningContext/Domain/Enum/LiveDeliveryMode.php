<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum LiveDeliveryMode: string
{
    case ONLINE = 'ONLINE';
    case IN_PERSON = 'IN_PERSON';
    case HYBRID = 'HYBRID';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE => 'En ligne',
            self::IN_PERSON => 'Présentiel',
            self::HYBRID => 'Hybride',
        };
    }

    public function requiresLocation(): bool
    {
        return in_array($this, [self::IN_PERSON, self::HYBRID], true);
    }

    public function requiresJoinUrl(): bool
    {
        return in_array($this, [self::ONLINE, self::HYBRID], true);
    }
}
