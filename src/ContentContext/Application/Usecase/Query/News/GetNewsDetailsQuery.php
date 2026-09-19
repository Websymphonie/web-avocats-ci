<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\News;

final readonly class GetNewsDetailsQuery
{
    public function __construct(public int $id) {}
}
