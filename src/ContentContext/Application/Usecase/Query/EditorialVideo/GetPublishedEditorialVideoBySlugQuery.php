<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo;

final readonly class GetPublishedEditorialVideoBySlugQuery
{
    public function __construct(public string $slug)
    {
    }
}
