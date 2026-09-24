<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Enum;

enum VideoProvider: string
{
    case YOUTUBE = 'YOUTUBE';
    case MUX = 'MUX';
    case CLOUDFLARE_STREAM = 'CLOUDFLARE_STREAM';
}
