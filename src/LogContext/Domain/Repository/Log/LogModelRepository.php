<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Repository\Log;

use Doctrine\ORM\Query;
use Websymphonie\LogContext\Application\Usecase\Query\Log\GetLogListQuery;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;

interface LogModelRepository
{
    public function create(Logs $entity): Logs;

    public function update(Logs $entity): Logs;

    public function remove(Logs $entity): void;

    public function getById(int $id): ?Logs;

    /** @return Query<mixed, mixed> */
    public function getLogQuery(GetLogListQuery $query): Query;

    public function count(): int;
}
