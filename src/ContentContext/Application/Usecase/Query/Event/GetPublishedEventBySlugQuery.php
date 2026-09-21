<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Event;

final readonly class GetPublishedEventBySlugQuery
{
    public function __construct(public string $slug)
    {
    }
}
