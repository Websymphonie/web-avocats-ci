<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\QueryHandler\Log;

use Websymphonie\LogContext\Application\Usecase\Query\Log\GetLogDetailsQuery;
use Websymphonie\LogContext\Domain\Repository\Log\LogModelRepository;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetLogDetailsQueryHandler implements QueryHandler
{
    public function __construct(private LogModelRepository $repository)
    {
    }

    public function __invoke(GetLogDetailsQuery $query): Logs
    {
        return $this->repository->getById($query->logId);
    }
}