<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Listener\AuthLog;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: AuthLog::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: AuthLog::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: AuthLog::class)]
readonly class AuthLogListener
{
    public function __construct(private CacheServiceInterface $cacheService)
    {
    }

    public function postPersist(AuthLog $authLog): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        $this->cacheService->invalidateTag(CacheEnum::CACHE_LIST_AUTH_LOG->value);
    }

    public function postUpdate(AuthLog $authLog): void
    {
        $this->clearCache();
    }

    public function postRemove(AuthLog $authLog): void
    {
        $this->clearCache();
    }
}
