<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Attribute;

use Attribute;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;

/**
 * Attribut personnalisé pour vérifier les accès par groupe de rôles.
 *
 * Exemple :
 *   #[HasGroupAccess(RoleGroupEnum::AGENCE)]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class HasGroupAccess
{
    public function __construct(
        public RoleGroupEnum|PermissionEnum $group
    )
    {
    }
}
