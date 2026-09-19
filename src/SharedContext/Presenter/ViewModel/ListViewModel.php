<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\ViewModel;

use Websymphonie\SharedContext\Domain\ViewModel\SimpleListViewModel;

class ListViewModel extends SimpleListViewModel
{
    /**
     * @param array<int, mixed> $items
     */
    public function __construct(array $items)
    {
        parent::__construct($items);
    }
}
