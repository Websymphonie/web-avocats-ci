<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Enum;

enum PhotoGalleryStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';
}
