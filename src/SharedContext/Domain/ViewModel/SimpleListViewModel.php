<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\ViewModel;

/**
 * Représente une simple liste d'éléments sans pagination.
 */
abstract class SimpleListViewModel
{
    /**
     * @var array<int, mixed>
     */
    public array $items;

    /** @param array<int, mixed> $items */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }
}
