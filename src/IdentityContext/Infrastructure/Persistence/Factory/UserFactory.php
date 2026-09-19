<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Factory;

use Websymphonie\IdentityContext\Domain\Model\User\UserModel;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

readonly class UserFactory
{
    /**
     * @param list<User> $entities
     * @return list<UserModel>
     */
    public static function fromEntityList(array $entities): array
    {
        return array_map(fn(User $user) => self::fromEntity($user), $entities);
    }

    public static function fromEntity(?User $user): ?UserModel
    {
        if ($user === null) {
            return null;
        }
        return new UserModel(
            id: $user->getId(),
            uuid: $user->getUuidAsString(),
            name: $user->getName() ?? $user->getEmail(),
            email: $user->getEmail(),
            roles: $user->getRoles(),
            enabled: $user->getEnabled(),
            createdAt: $user->getCreatedAt(),
            updatedAt: $user->getUpdatedAt(),
        );
    }
}
