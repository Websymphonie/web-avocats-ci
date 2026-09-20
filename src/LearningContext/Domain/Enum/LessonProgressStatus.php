<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum LessonProgressStatus: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminée',
        };
    }
}
