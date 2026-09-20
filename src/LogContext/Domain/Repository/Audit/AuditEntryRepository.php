<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Repository\Audit;

use Websymphonie\LogContext\Domain\Model\Audit\AuditEntry;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntryPage;

interface AuditEntryRepository
{
    public function record(AuditEntry $entry): AuditEntry;

    public function findById(int $id): ?AuditEntry;

    public function findByDeduplicationKey(string $deduplicationKey): ?AuditEntry;

    public function findPage(AuditEntrySearchCriteria $criteria): AuditEntryPage;
}
