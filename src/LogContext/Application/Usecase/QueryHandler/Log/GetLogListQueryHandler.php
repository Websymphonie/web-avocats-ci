<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\QueryHandler\Log;

use Knp\Component\Pager\PaginatorInterface;
use Websymphonie\LogContext\Application\Usecase\Query\Log\GetLogListQuery;
use Websymphonie\LogContext\Domain\Repository\Log\LogModelRepository;
use Websymphonie\LogContext\Infrastructure\Persistence\Factory\Log\LogFactory;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

final readonly class GetLogListQueryHandler implements QueryHandler
{
    public function __construct(
        private PaginatorInterface    $paginator,
        private LogFactory            $factory,
        private CacheServiceInterface $cacheService,
        private LogModelRepository    $repository,
    )
    {
    }

    public function __invoke(GetLogListQuery $query): PaginateListViewModel
    {
        $cacheKeyParts = [
            CacheEnum::CACHE_LIST_LOG->value,
            $query->page,
            $query->limit,
            $query->message ?? 'null',
            $query->levelName ?? 'null',
        ];

        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_LIST_LOG->value];

        $pagination = $this->cacheService->getCache($cacheKey, function () use ($query) {
            $data = $this->paginator->paginate(
                $this->repository->getLogQuery(query: $query),
                $query->page,
                $query->limit
            );
            $logModels = $this->factory->fromEntityList($data->getItems());
            $data->setItems($logModels);
            return $data;
        }, $tags);

        return new PaginateListViewModel(pagination: $pagination);
    }
}
