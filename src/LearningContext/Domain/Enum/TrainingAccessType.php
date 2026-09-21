<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum TrainingAccessType: string
{
    case FREE = 'FREE';
    case PAID = 'PAID';
    case RESTRICTED = 'RESTRICTED';

    public function badge(): string
    {
        return match ($this) {
            self::FREE => ColorEnum::WARNING->value,
            self::PAID => ColorEnum::SUCCESS->value,
            self::RESTRICTED => ColorEnum::DANGER->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Gratuit',
            self::PAID => 'Payant',
            self::RESTRICTED => 'Restreint',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FREE => 'Aucun paiement ne sera nécessaire.',
            self::PAID => 'Un paiement confirmé sera nécessaire ultérieurement.',
            self::RESTRICTED => 'L’accès sera accordé selon une règle métier future.',
        };
    }
}
