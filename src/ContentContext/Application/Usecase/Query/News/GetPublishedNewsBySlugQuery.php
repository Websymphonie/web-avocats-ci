<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\News;

final readonly class GetPublishedNewsBySlugQuery
{
    public function __construct(public string $slug)
    {
    }
}
