<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Tiwg\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Service\User\PermissionsInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final class UserPermissionsExtension extends AbstractExtension
{
    public function __construct(
        private readonly PermissionsInterface $permissions
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canList', fn(User $u) => $this->can($u, PermissionEnum::LIST)),
            new TwigFunction('canView', fn(User $u) => $this->can($u, PermissionEnum::VIEW)),
            new TwigFunction('canCreate', fn(User $u) => $this->can($u, PermissionEnum::CREATE)),
            new TwigFunction('canEdit', fn(User $u) => $this->can($u, PermissionEnum::EDIT)),
            new TwigFunction('canPrint', fn(User $u) => $this->can($u, PermissionEnum::PRINT)),
            new TwigFunction('canDelete', fn(User $u) => $this->can($u, PermissionEnum::DELETE)),

            // Nouveau (recommandé)
            new TwigFunction('can', [$this, 'can']),
            new TwigFunction('userPermissions', [$this, 'permissions']),
        ];
    }

    public function can(User $user, PermissionEnum|string $permission): bool
    {
        if (is_string($permission)) {
            $permission = PermissionEnum::from($permission);
        }

        return $this->permissions->has($user, $permission);
    }

    /** @return list<PermissionEnum> */
    public function permissions(User $user): array
    {
        return $this->permissions->permissions($user);
    }
}
