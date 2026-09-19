<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Listener\Log;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Logs::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Logs::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Logs::class)]
readonly class LogListener
{
    public function __construct(private CacheServiceInterface $cacheService)
    {
    }

    public function postPersist(Logs $log): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        $this->cacheService->invalidateTag(CacheEnum::CACHE_LIST_LOG->value);
    }

    public function postUpdate(Logs $log): void
    {
        $this->clearCache();
    }

    public function postRemove(Logs $log): void
    {
        $this->clearCache();
    }
}
