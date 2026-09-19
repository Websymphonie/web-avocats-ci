<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum TrainingVisibility: string
{
    case PUBLIC = 'PUBLIC';
    case MEMBER = 'MEMBER';

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
