<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\QueryHandler\User;

use Knp\Component\Pager\PaginatorInterface;
use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserListQuery;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\UserFactory;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

final readonly class GetUserListQueryHandler implements QueryHandler
{
    public function __construct(
        private PaginatorInterface           $paginator,
        private CacheServiceInterface        $cacheService,
        private UserModelRepositoryInterface $repository,
        private UserFactory                  $factory,
    )
    {
    }

    public function __invoke(GetUserListQuery $query): PaginateListViewModel
    {
        $cacheKeyParts = [
            CacheEnum::CACHE_USERS_LIST->value,
            $query->page,
            $query->limit,
            $query->name ?? 'null',
            $query->role ?? 'null',
            $query->email ?? 'null',
            $query->enabled === null ? 'all' : ($query->enabled ? 'enabled' : 'disabled')
        ];

        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_USERS_LIST->value];

        $pagination = $this->cacheService->getCache($cacheKey, function () use ($query) {
            $data = $this->paginator->paginate(
                $this->repository->getUserQuery(query: $query),
                $query->page,
                $query->limit
            );
            $userModels = $this->factory->fromEntityList($data->getItems());
            $data->setItems($userModels);
            return $data;
        }, $tags);
        return new PaginateListViewModel(pagination: $pagination);
    }
}
