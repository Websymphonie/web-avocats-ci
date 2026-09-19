<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command;

final readonly class BulkDeleteTrainingsCommand
{
    /** @param list<int> $ids */
    public function __construct(public array $ids) {}
}
