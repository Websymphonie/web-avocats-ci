<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum PageStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';

    public function badge(): string
    {
        return match ($this) {
            self::DRAFT => ColorEnum::WARNING->value,
            self::PUBLISHED => ColorEnum::SUCCESS->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publiée',
        };
    }
}
