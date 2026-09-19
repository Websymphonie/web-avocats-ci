<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Listener\Image;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Images::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Images::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Images::class)]
readonly class ImageListener
{
    public function __construct(private CacheServiceInterface $cacheService)
    {
    }

    public function postPersist(Images $image): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        $this->cacheService->invalidateTag(CacheEnum::CACHE_LIST_IMAGE->value);
    }

    public function postUpdate(Images $image): void
    {
        $this->clearCache();
    }

    public function postRemove(Images $image): void
    {
        $this->clearCache();
    }
}
