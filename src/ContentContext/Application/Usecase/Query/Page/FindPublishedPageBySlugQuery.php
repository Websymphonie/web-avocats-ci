<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Page;

use Websymphonie\ContentContext\Domain\Enum\PageGroup;

final readonly class FindPublishedPageBySlugQuery
{
    public function __construct(public string $slug, public ?PageGroup $group = null) {}
}
