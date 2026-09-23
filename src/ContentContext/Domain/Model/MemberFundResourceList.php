<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

final readonly class MemberFundResourceList
{
    /** @param list<MemberFundResource> $items */
    public function __construct(
        public array $items,
        public int $totalItemCount,
        public int $page,
        public int $itemNumberPerPage,
    ) {}

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->totalItemCount / $this->itemNumberPerPage));
    }
}
