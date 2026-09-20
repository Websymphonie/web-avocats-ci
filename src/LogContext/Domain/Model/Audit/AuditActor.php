<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Model\Audit;

use InvalidArgumentException;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;

final readonly class AuditActor
{
    public function __construct(
        public AuditActorType $type,
        public ?string $id = null,
    ) {
        if ($id !== null && (trim($id) === '' || strlen($id) > 191)) {
            throw new InvalidArgumentException('L’identifiant de l’acteur d’audit est invalide.');
        }

        if ($type === AuditActorType::USER && $id === null) {
            throw new InvalidArgumentException('Un acteur utilisateur doit avoir un identifiant.');
        }
    }
}
