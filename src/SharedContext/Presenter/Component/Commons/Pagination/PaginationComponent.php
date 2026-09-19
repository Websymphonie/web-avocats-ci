<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Component\Commons\Pagination;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

#[AsTwigComponent('PaginationComponent', template: 'shared/components/pagination/pagination_component.html.twig')]
class PaginationComponent
{
    public PaginateListViewModel $pagination;
}
