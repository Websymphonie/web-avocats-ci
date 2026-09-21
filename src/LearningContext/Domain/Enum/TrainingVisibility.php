<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum TrainingVisibility: string
{
    case PUBLIC = 'PUBLIC';
    case MEMBER = 'MEMBER';

    public function badge(): string
    {
        return match ($this) {
            self::PUBLIC => ColorEnum::PRIMARY->value,
            self::MEMBER => ColorEnum::SUCCESS->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Public',
            self::MEMBER => 'Membres',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PUBLIC => 'La formation pourra être visible publiquement.',
            self::MEMBER => 'La formation sera visible uniquement par les membres.',
        };
    }
}
