<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\QueryHandler\AuthLog;


use Websymphonie\LogContext\Application\Usecase\Query\AuthLog\GetAuthLogDetailsQuery;
use Websymphonie\LogContext\Domain\Repository\AuthLog\AuthLogModelRepository;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetAuthLogDetailsQueryHandler implements QueryHandler
{
    public function __construct(private AuthLogModelRepository $repository)
    {
    }

    public function __invoke(GetAuthLogDetailsQuery $query): AuthLog
    {
        return $this->repository->getById($query->authLogId);
    }
}