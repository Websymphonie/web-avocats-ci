<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

final readonly class MemberPaymentList
{
    /**
     * @param list<MemberPaymentSummary> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function pageCount(): int
    {
        return max(1, (int) ceil($this->total / $this->limit));
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->pageCount();
    }
}
