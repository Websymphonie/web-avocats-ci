<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Listener\Reglage;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Reglages::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Reglages::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Reglages::class)]
readonly class ReglageListener
{
    public function __construct(private CacheServiceInterface $cacheService)
    {
    }

    public function postPersist(Reglages $reglage): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        $this->cacheService->invalidateTag(CacheEnum::CACHE_LIST_REGLAGE->value);
    }

    public function postUpdate(Reglages $reglage): void
    {
        $this->clearCache();
    }

    public function postRemove(Reglages $reglage): void
    {
        $this->clearCache();
    }
}
