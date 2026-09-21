<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum NewsStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';

    public function badge(): string
    {
        return match ($this) {
            self::DRAFT => ColorEnum::WARNING->value,
            self::PUBLISHED => ColorEnum::SUCCESS->value,
            self::ARCHIVED => ColorEnum::PURPLE->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publié',
            self::ARCHIVED => 'Archivé',
        };
    }
}
