<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Model\Audit;

use InvalidArgumentException;

final readonly class AuditContext
{
    public function __construct(public string $value)
    {
        if (!preg_match('/^[A-Z][A-Z0-9_]{0,49}$/', $value)) {
            throw new InvalidArgumentException('Le contexte d’audit doit être un identifiant métier en majuscules.');
        }
    }
}
