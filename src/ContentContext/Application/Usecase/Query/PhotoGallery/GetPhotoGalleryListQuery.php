<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\PhotoGallery;

use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;

final class GetPhotoGalleryListQuery
{
    public function __construct(public ?string $search = null, public ?PhotoGalleryStatus $status = null, public ?int $tagId = null, public int $page = 1, public int $limit = 20) {}
}
