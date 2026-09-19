<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Abstract;

abstract class PaginateListViewAbstract
{
    /** @var array<int, mixed> */
    public array $items;
    public int $totalItemCount;
    public int $page;
    public int $lastPage;
    public int $itemNumberPerPage;

    /** @param array<int, mixed> $items */
    public function __construct(
        array $items,
        int   $totalItemCount,
        int   $page,
        int   $lastPage,
        int   $itemNumberPerPage
    )
    {
        $this->items = $items;
        $this->totalItemCount = $totalItemCount;
        $this->page = $page;
        $this->lastPage = $lastPage;
        $this->itemNumberPerPage = $itemNumberPerPage;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }
}
