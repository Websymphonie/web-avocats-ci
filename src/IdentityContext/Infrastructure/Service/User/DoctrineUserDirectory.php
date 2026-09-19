<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Service\User;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryUser;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final readonly class DoctrineUserDirectory implements UserDirectoryInterface
{
    public function __construct(private UserModelRepositoryInterface $repository) {}

    public function getById(int $userId): ?UserDirectoryUser
    {
        try { return $this->map($this->repository->getById($userId)); } catch (\Throwable) { return null; }
    }

    /** @return list<UserDirectoryUser> */
    public function getByIds(array $userIds): array
    {
        return array_map(fn (User $user): UserDirectoryUser => $this->map($user), $this->repository->findByIds(array_values(array_unique($userIds))));
    }

    public function search(string $term, int $limit = 100): array
    {
        return array_map(fn (User $user): UserDirectoryUser => $this->map($user), $this->repository->searchActive($term, $limit));
    }

    private function map(User $user): UserDirectoryUser
    {
        return new UserDirectoryUser($user->getId() ?? 0, $user->getUuidAsString() ?? '', $user->getName() ?? '', $user->getUserIdentifier(), (bool) $user->getEnabled());
    }
}
