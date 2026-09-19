<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\QueryHandler\Reglage;


use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\ReglageListQuery;
use Websymphonie\AdminContext\Domain\Repository\Reglage\ReglageModelRepository;
use Websymphonie\AdminContext\Infrastructure\Persistence\Factory\ReglageFactory;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\ListViewModel;

readonly class ReglageListQueryHandler implements QueryHandler
{
    public function __construct(
        private ReglageModelRepository $repository,
        private CacheServiceInterface  $cacheService,
    )
    {
    }

    public function __invoke(ReglageListQuery $query): ListViewModel
    {
        $cacheKeyParts = [CacheEnum::CACHE_LIST_REGLAGE->value];
        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_LIST_REGLAGE->value];

        return $this->cacheService->getCache($cacheKey, function () use ($query) {
            $data = $this->repository->findALLForTwig(query: $query);
            $items = ReglageFactory::fromEntityList($data);
            return new ListViewModel(items: $items);
        }, $tags);


    }
}
