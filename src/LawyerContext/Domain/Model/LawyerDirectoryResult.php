<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Domain\Model;

final readonly class LawyerDirectoryResult
{
    /** @param list<LawyerDirectoryEntry> $items */
    public function __construct(
        public array $items,
        public int $totalItemCount,
        public int $page,
        public int $itemNumberPerPage,
    ) {
    }

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->totalItemCount / $this->itemNumberPerPage));
    }
}
