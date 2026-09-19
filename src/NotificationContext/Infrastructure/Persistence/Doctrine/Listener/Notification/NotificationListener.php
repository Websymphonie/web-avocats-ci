<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Listener\Notification;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Notifications::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Notifications::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Notifications::class)]
readonly class NotificationListener
{
    public function __construct(private CacheServiceInterface $cacheService)
    {
    }

    public function postPersist(Notifications $notification): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        $this->cacheService->invalidateTag(CacheEnum::CACHE_NOTIFICATION_LIST->value);
    }

    public function postUpdate(Notifications $notification): void
    {
        $this->clearCache();
    }

    public function postRemove(Notifications $notification): void
    {
        $this->clearCache();
    }
}
