<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Listener\User;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: User::class)]
readonly class UserListener
{
    public function __construct(private CacheServiceInterface $cacheService)
    {
    }

    public function postPersist(User $user): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        $this->cacheService->invalidateTag(CacheEnum::CACHE_USERS_LIST->value);
    }

    public function postUpdate(User $user): void
    {
        $this->clearCache();
    }

    public function postRemove(User $user): void
    {
        $this->clearCache();
    }
}
