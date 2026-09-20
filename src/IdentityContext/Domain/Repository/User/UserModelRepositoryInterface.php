<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Repository\User;

use Doctrine\ORM\Query;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserListQuery;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

interface UserModelRepositoryInterface
{
    public function getByEmail(string $email): ?User;

    public function getById(int $id): User;

    public function getByUuid(string $uuid): ?User;

    /**
     * @param list<string> $uuids
     * @return list<User>
     */
    public function findByUuids(array $uuids): array;

    /**
     * @param list<string> $roles
     * @return list<User>
     */
    public function findByRoles(array $roles): array;

    /**
     * @param list<int> $ids
     * @return list<User>
     */
    public function findByIds(array $ids): array;

    /** @return list<User> */
    public function searchActive(string $term, int $limit = 100): array;

    public function create(User $entity): User;

    public function update(User $entity): User;

    public function remove(User $entity): void;

    public function createCommandProfileFromUser(int $id): UpdateProfileCommand;

    public function createCommandFromUser(int $id): UpdateUserCommand;

    /** @return Query<mixed, mixed> */
    public function getUserQuery(GetUserListQuery $query): Query;

    public function countAll(bool $onlyDisabled = false): int;

    public function countActiveSuperAdmins(): int;

}
