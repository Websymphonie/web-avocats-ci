<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\QueryHandler\Image;


use Websymphonie\AdminContext\Application\Usecase\Query\Image\ImageListQuery;
use Websymphonie\AdminContext\Domain\Repository\Image\ImageModelRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\ListViewModel;

readonly class ImageListQueryHandler implements QueryHandler
{
    public function __construct(
        private ImageModelRepositoryInterface $repository,
        private CacheServiceInterface         $cacheService,
    )
    {
    }

    public function __invoke(ImageListQuery $query): ListViewModel
    {
        $cacheKeyParts = [CacheEnum::CACHE_LIST_IMAGE->value];
        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_LIST_IMAGE->value];

        return $this->cacheService->getCache($cacheKey, function () use ($query) {
            $items = $this->repository->findALLForTwig(query: $query);
            return new ListViewModel(items: $items);
        }, $tags);
    }
}
