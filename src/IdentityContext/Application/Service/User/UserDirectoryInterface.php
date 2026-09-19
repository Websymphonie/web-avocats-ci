<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\User;

interface UserDirectoryInterface
{
    public function getById(int $userId): ?UserDirectoryUser;

    /**
     * @param list<int> $userIds
     * @return list<UserDirectoryUser>
     */
    public function getByIds(array $userIds): array;

    /** @return list<UserDirectoryUser> */
    public function search(string $term, int $limit = 100): array;
}
