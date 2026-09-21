<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum TrainingType: string
{
    case COURSE = 'COURSE';
    case LIVE = 'LIVE';

    public function badge(): string
    {
        return match ($this) {
            self::COURSE => ColorEnum::SUCCESS->value,
            self::LIVE => ColorEnum::PINK->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::COURSE => 'Cours',
            self::LIVE => 'Live',
        };
    }
}
