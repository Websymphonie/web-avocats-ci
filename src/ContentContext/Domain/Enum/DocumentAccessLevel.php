<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

enum DocumentAccessLevel: string
{
    case PUBLIC = 'PUBLIC';
    case MEMBER = 'MEMBER';
    case RESTRICTED = 'RESTRICTED';
    case PRIVATE = 'PRIVATE';
}
