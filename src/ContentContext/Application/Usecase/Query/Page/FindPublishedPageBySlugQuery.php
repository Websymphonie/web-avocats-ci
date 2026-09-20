<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Page;

final readonly class FindPublishedPageBySlugQuery
{
    public function __construct(public string $slug) {}
}
