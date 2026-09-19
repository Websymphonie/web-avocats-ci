<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Service\Role;

use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;

final class PermissionCatalog
{
    /** @return array<string, array<string, string>> */
    public static function groupedChoices(): array
    {
        $groups = [];
        foreach (PermissionEnum::configurableCases() as $permission) {
            $groups[self::group($permission)][self::label($permission)] = $permission->value;
        }

        return $groups;
    }

    private static function group(PermissionEnum $permission): string
    {
        return match ($permission) {
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::DELETE,
            PermissionEnum::PRINT => 'Administration',
            PermissionEnum::ROLE_MANAGE => 'Administration',
            PermissionEnum::CONTENT_NEWS_VIEW,
            PermissionEnum::CONTENT_NEWS_MANAGE,
            PermissionEnum::CONTENT_NEWS_PUBLISH,
            PermissionEnum::CONTENT_NEWS_DELETE => 'Contenu · Actualités',
        };
    }

    private static function label(PermissionEnum $permission): string
    {
        return $permission->label();
    }
}
