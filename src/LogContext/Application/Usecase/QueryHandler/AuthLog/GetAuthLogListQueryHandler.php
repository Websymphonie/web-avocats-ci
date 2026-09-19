<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\QueryHandler\AuthLog;

use Knp\Component\Pager\PaginatorInterface;
use Websymphonie\LogContext\Application\Usecase\Query\AuthLog\GetAuthLogListQuery;
use Websymphonie\LogContext\Domain\Repository\AuthLog\AuthLogModelRepository;
use Websymphonie\LogContext\Infrastructure\Persistence\Factory\AuthLog\AuthLogFactory;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

final readonly class GetAuthLogListQueryHandler implements QueryHandler
{
    public function __construct(
        private PaginatorInterface     $paginator,
        private AuthLogFactory         $factory,
        private CacheServiceInterface  $cacheService,
        private AuthLogModelRepository $repository,
    )
    {
    }

    public function __invoke(GetAuthLogListQuery $query): PaginateListViewModel
    {
        $cacheKeyParts = [
            CacheEnum::CACHE_LIST_AUTH_LOG->value,
            $query->page,
            $query->limit,
            $query->userIp ?? 'null',
            $query->emailEntered ?? 'null',
        ];

        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_LIST_AUTH_LOG->value];

        $pagination = $this->cacheService->getCache($cacheKey, function () use ($query) {
            $data = $this->paginator->paginate(
                $this->repository->getAuthLogQuery(query: $query),
                $query->page,
                $query->limit
            );
            $authLogModels = $this->factory->fromEntityList($data->getItems());
            $data->setItems($authLogModels);
            return $data;
        }, $tags);

        return new PaginateListViewModel(pagination: $pagination);
    }
}