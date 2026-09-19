<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum TrainingType: string
{
    case COURSE = 'COURSE';
    case LIVE = 'LIVE';

    public function label(): string
    {
        return match ($this) {
            self::COURSE => 'Cours',
            self::LIVE => 'Live',
        };
    }
}
