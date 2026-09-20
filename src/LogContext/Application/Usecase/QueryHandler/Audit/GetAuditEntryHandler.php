<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\QueryHandler\Audit;

use RuntimeException;
use Websymphonie\LogContext\Application\Usecase\Query\Audit\GetAuditEntryQuery;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntry;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntryRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetAuditEntryHandler implements QueryHandler
{
    public function __construct(private AuditEntryRepository $repository)
    {
    }

    public function __invoke(GetAuditEntryQuery $query): AuditEntry
    {
        $entry = $this->repository->findById($query->id);
        if ($entry === null) {
            throw new RuntimeException('Audit entry not found.');
        }

        return $entry;
    }
}
