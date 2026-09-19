<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\ViewModel;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Websymphonie\SharedContext\Domain\Abstract\PaginateListViewAbstract;

class PaginateListViewModel extends PaginateListViewAbstract
{
    /** @var PaginationInterface<int, mixed> */
    public PaginationInterface $pagination;

    /**
     * @param PaginationInterface<int, mixed> $pagination
     */
    public function __construct(PaginationInterface $pagination)
    {
        $this->pagination = $pagination;
        parent::__construct(
            items: $pagination->getItems(),
            totalItemCount: $pagination->getTotalItemCount(),
            page: $pagination->getCurrentPageNumber(),
            lastPage: intval(ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())),
            itemNumberPerPage: $pagination->getItemNumberPerPage(),
        );
    }
}
