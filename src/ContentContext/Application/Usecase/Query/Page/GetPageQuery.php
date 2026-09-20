<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Page;

final readonly class GetPageQuery
{
    public function __construct(public int $id) {}
}
