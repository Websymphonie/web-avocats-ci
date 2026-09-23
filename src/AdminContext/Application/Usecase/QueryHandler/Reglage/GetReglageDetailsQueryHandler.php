<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\QueryHandler\Reglage;

use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\GetReglageDetailsQuery;
use Websymphonie\AdminContext\Domain\Repository\Reglage\ReglageModelRepository;
use Websymphonie\AdminContext\Domain\Model\Reglage\ReglageModel;
use Websymphonie\AdminContext\Application\Service\SystemBootstrapData;
use Websymphonie\AdminContext\Infrastructure\Persistence\Factory\ReglageFactory;
use Websymphonie\AdminContext\Presenter\ViewModel\Reglage\ReglageDetailViewModel;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

final readonly class GetReglageDetailsQueryHandler implements QueryHandler
{
    public function __construct(
        private CacheServiceInterface  $cacheService,
        private ReglageModelRepository $repository,
    )
    {
    }

    public function __invoke(GetReglageDetailsQuery $query): ReglageDetailViewModel
    {
        $cacheKeyParts = [CacheEnum::CACHE_DETAIL_REGLAGE->withString($query->name)];
        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_LIST_REGLAGE->value];

        return $this->cacheService->getCache($cacheKey, function () use ($query) {
            $entity = $this->repository->getValue($query->name);
            $reglageModel = $entity === null
                ? new ReglageModel(
                    name: $query->name,
                    label: $query->name,
                    value: SystemBootstrapData::fallbackSettingValue($query->name),
                    type: 'text',
                )
                : ReglageFactory::fromEntity($entity);
            return new ReglageDetailViewModel($reglageModel);
        }, $tags);
    }
}
