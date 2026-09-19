<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Tiwg\Extension;

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User\UserRoleResolver;

final class RolesExtension extends AbstractExtension
{
    public function __construct(
        private readonly UserRoleResolver $resolver
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('mainRole', [$this, 'mainRole']),
            new TwigFunction('roleLabel', [$this, 'label']),
            new TwigFunction('roleBadgeClass', [$this, 'badge']),
            new TwigFunction('isRole', [$this, 'isRole']),
            new TwigFunction('hasRole', [$this, 'hasRole']),

            new TwigFunction('isSuperAdmin', fn(User $u) => $this->hasRole($u, UserRolesEnum::SUPER_ADMIN)),
            new TwigFunction('isAdmin', fn(User $u) => $this->hasRole($u, UserRolesEnum::ADMIN)),
            new TwigFunction('isAvocat', fn(User $u) => $this->hasRole($u, UserRolesEnum::AVOCAT)),
            new TwigFunction('isUser', fn(User $u) => $this->hasRole($u, UserRolesEnum::USER)),
        ];
    }

    public function hasRole(User $user, UserRolesEnum $role): bool
    {
        return in_array($role->value, $user->getRoles(), true);
    }

    public function mainRole(User $user): ?UserRolesEnum
    {
        return $this->resolver->resolveMain($user->getRoles());
    }

    public function label(string $role): ?string
    {
        return UserRolesEnum::tryFrom($role)?->label();
    }

    /**
     * @throws Exception
     */
    public function badge(string $role): string
    {
        return UserRolesEnum::tryFrom($role)?->badge()->value ?? 'secondary';
    }

    public function isSuperAdmin(User $user): bool
    {
        return $this->hasRole($user, UserRolesEnum::SUPER_ADMIN);
    }

    public function isRole(User $user, UserRolesEnum $role): bool
    {
        return $this->resolver->resolveMain($user->getRoles()) === $role;
    }

    public function isAdmin(User $user): bool
    {
        return $this->hasRole($user, UserRolesEnum::ADMIN);
    }

    public function isAvocat(User $user): bool
    {
        return $this->hasRole($user, UserRolesEnum::AVOCAT);
    }

    public function isUser(User $user): bool
    {
        return $this->hasRole($user, UserRolesEnum::USER);
    }
}