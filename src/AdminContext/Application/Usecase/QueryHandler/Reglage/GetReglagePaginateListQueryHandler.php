<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\QueryHandler\Reglage;

use Knp\Component\Pager\PaginatorInterface;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\GetReglagePaginateListQuery;
use Websymphonie\AdminContext\Domain\Repository\Reglage\ReglageModelRepository;
use Websymphonie\AdminContext\Infrastructure\Persistence\Factory\ReglageFactory;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

final readonly class GetReglagePaginateListQueryHandler implements QueryHandler
{
    public function __construct(
        private PaginatorInterface     $paginator,
        private ReglageModelRepository $repository,
        private CacheServiceInterface  $cacheService,
    )
    {
    }

    public function __invoke(GetReglagePaginateListQuery $query): PaginateListViewModel
    {
        $cacheKeyParts = [
            CacheEnum::CACHE_LIST_PAGINATE_PAGE->value,
            $query->page,
            $query->limit,
        ];

        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_LIST_REGLAGE->value];

        $pagination = $this->cacheService->getCache($cacheKey, function () use ($query) {
            $data = $this->paginator->paginate(
                $this->repository->getReglageQuery(query: $query),
                $query->page,
                $query->limit
            );
            $reglageModels = ReglageFactory::fromEntityList($data->getItems());
            $data->setItems($reglageModels);
            return $data;
        }, $tags);

        return new PaginateListViewModel(pagination: $pagination);
    }
}