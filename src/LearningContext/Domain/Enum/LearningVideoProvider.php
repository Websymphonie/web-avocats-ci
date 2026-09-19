<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum LearningVideoProvider: string
{
    case YOUTUBE = 'YOUTUBE';

    public function label(): string
    {
        return 'YouTube';
    }
}
