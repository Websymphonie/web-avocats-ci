<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\QueryHandler\Maintenance;

use Websymphonie\AdminContext\Application\Usecase\Query\Maintenance\GetMaintenanceDetailsQuery;
use Websymphonie\AdminContext\Domain\Repository\Maintenance\MaintenanceModelRepository;
use Websymphonie\AdminContext\Presenter\ViewModel\Maintenance\MaintenanceDetailViewModel;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

final readonly class MaintenanceDetailsQueryHandler implements QueryHandler
{
    public function __construct(
        private MaintenanceModelRepository $repository,
        private CacheServiceInterface      $cacheService,
    )
    {
    }

    public function __invoke(GetMaintenanceDetailsQuery $query): MaintenanceDetailViewModel
    {
        $cacheKeyParts = [CacheEnum::CACHE_DETAIL_MAINTENANCE->with($query->id)];
        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_DETAIL_MAINTENANCE->value];

        return $this->cacheService->getCache($cacheKey, function () use ($query) {
            $imageModel = $this->repository->getById($query->id);
            return new MaintenanceDetailViewModel($imageModel);
        }, $tags);
    }
}
