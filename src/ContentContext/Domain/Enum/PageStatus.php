<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

enum PageStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publiée',
        };
    }
}
