<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Repository\AuthLog;


use Doctrine\ORM\Query;
use Websymphonie\LogContext\Application\Usecase\Query\AuthLog\GetAuthLogListQuery;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;

interface AuthLogModelRepository
{
    public function addFailedAuthAttempt(
        string  $emailEntered,
        ?string $userIP
    ): void;

    public function addSuccessFulAuthAttempt(
        string  $emailEntered,
        ?string $userIP,
        bool    $isRememberMeAuth = false
    ): void;

    public function addSuccessFulLogouthAttempt(
        string  $emailEntered,
        ?string $userIP,
        bool    $isRememberMeAuth = false
    ): void;

    public function create(AuthLog $entity): AuthLog;

    public function update(AuthLog $entity): AuthLog;

    public function remove(AuthLog $entity): void;

    public function getById(int $id): ?AuthLog;

    /** @return Query<mixed, mixed> */
    public function getAuthLogQuery(GetAuthLogListQuery $query): Query;

    public function count(): int;
}
