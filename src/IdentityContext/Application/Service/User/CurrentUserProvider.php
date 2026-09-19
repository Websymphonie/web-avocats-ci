<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\User;

use Websymphonie\IdentityContext\Domain\Model\User\UserModel;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

interface CurrentUserProvider
{
    public function getUser(): ?User;

    public function user(): ?UserModel;
}