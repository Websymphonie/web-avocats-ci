<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\QueryHandler\Audit;

use DateTimeImmutable;
use Websymphonie\LogContext\Application\Usecase\Query\Audit\GetAuditEntryListQuery;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntryPage;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntryRepository;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntrySearchCriteria;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetAuditEntryListHandler implements QueryHandler
{
    public function __construct(private AuditEntryRepository $repository)
    {
    }

    public function __invoke(GetAuditEntryListQuery $query): AuditEntryPage
    {
        return $this->repository->findPage(new AuditEntrySearchCriteria(
            page: max(1, $query->page),
            limit: max(1, min(100, $query->limit)),
            from: $this->parseDate($query->from, false),
            to: $this->parseDate($query->to, true),
            context: $query->context !== null ? strtoupper(trim($query->context)) : null,
            action: $this->normalize($query->action),
            actorId: $this->normalize($query->actorId),
            targetType: $this->normalize($query->targetType),
            targetId: $this->normalize($query->targetId),
        ));
    }

    private function normalize(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value === '' ? null : $value;
    }

    private function parseDate(?string $value, bool $endOfDay): ?DateTimeImmutable
    {
        $value = $this->normalize($value);
        if ($value === null) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false) {
            return null;
        }

        return $endOfDay ? $date->setTime(23, 59, 59) : $date;
    }
}
