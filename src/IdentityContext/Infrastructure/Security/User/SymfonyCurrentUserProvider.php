<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Security\User;

use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final readonly class SymfonyCurrentUserProvider implements CurrentUserProvider
{
    public function __construct(private Security $security)
    {
    }

    public function user(): ?UserModel
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user === null) {
            return null;
        }

        return new UserModel(
            id: $user->getId(),
            uuid: $user->getUuidAsString(),
            name: $user->getName(),
            email: $user->getUserIdentifier(),
            roles: $user->getRoles(),
            enabled: $user->getEnabled(),
            createdAt: $user->getCreatedAt(),
            updatedAt: $user->getUpdatedAt(),
        );
    }

    public function getUser(): ?User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        return $user;
    }
}
