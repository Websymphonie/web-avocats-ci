<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Model\Audit;

use InvalidArgumentException;

final readonly class AuditAction
{
    public function __construct(public string $value)
    {
        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $value) || strlen($value) > 150) {
            throw new InvalidArgumentException('L’action d’audit doit respecter le format métier attendu.');
        }
    }
}
