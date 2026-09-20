<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Actor;

interface CurrentActorProvider
{
    public function currentUserId(): ?int;
}
