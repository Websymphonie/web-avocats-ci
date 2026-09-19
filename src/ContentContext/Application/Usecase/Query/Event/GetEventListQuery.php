<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Event;

use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;

final class GetEventListQuery
{
    public function __construct(public ?string $search = null, public ?EventStatus $status = null, public ?EventFormat $format = null, public ?int $categoryId = null, public ?int $tagId = null, public int $page = 1, public int $limit = 20) {}
}
