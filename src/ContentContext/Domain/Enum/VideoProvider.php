<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

enum VideoProvider: string
{
    case YOUTUBE = 'YOUTUBE';
    case EXTERNAL_URL = 'EXTERNAL_URL';

    public function label(): string
    {
        return match ($this) {
            self::YOUTUBE => 'YouTube',
            self::EXTERNAL_URL => 'URL externe',
        };
    }
}
