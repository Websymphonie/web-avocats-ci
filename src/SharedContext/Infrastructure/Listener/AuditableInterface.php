<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Listener;

use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

interface AuditableInterface
{
    public function setCreatedBy(?User $user): self;

    public function setUpdatedBy(?User $user): self;
}