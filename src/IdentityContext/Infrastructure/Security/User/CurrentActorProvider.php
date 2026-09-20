<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Security\User;

use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider as CurrentActorProviderContract;

final readonly class CurrentActorProvider implements CurrentActorProviderContract
{
    public function __construct(private CurrentUserProvider $currentUserProvider)
    {
    }

    public function currentUserId(): ?int
    {
        return $this->currentUserProvider->user()?->id;
    }
}
