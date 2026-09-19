<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\QueryHandler\Image;

use Websymphonie\AdminContext\Application\Usecase\Query\Image\ImageDetailsQuery;
use Websymphonie\AdminContext\Domain\Repository\Image\ImageModelRepositoryInterface;
use Websymphonie\AdminContext\Presenter\ViewModel\Image\ImageDetailViewModel;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

final readonly class ImageDetailsQueryHandler implements QueryHandler
{
    public function __construct(
        private ImageModelRepositoryInterface $repository,
        private CacheServiceInterface         $cacheService,
    )
    {
    }

    public function __invoke(ImageDetailsQuery $query): ImageDetailViewModel
    {
        $cacheKeyParts = [CacheEnum::CACHE_DETAIL_IMAGE->withString($query->name)];
        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_LIST_IMAGE->value];

        return $this->cacheService->getCache($cacheKey, function () use ($query) {
            $imageModel = $this->repository->getValue($query->name);
            return new ImageDetailViewModel($imageModel);
        }, $tags);
    }
}
