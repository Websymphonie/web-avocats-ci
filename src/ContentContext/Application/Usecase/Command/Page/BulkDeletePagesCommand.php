<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Command\Page;

final readonly class BulkDeletePagesCommand
{
    /** @param list<int> $ids */
    public function __construct(public array $ids) {}
}
