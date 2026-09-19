<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\News;

final readonly class BulkDeleteNewsCommand
{
    /** @param list<int> $ids */
    public function __construct(public array $ids) {}
}
