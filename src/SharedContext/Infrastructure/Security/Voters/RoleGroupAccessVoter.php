<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Security\Voters;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Domain\Service\User\PermissionsInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

/** @extends Voter<string, string|RoleGroupEnum> */
class RoleGroupAccessVoter extends Voter
{
    public const string ROLE_GROUP_ACCESS = 'ROLE_GROUP_ACCESS';

    public function __construct(
        private readonly PermissionsInterface $permissions,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Le subject peut être soit une RoleGroupEnum, soit son nom en string
        return $attribute === self::ROLE_GROUP_ACCESS
            && (is_string($subject) || $subject instanceof RoleGroupEnum);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Les codes de permission persistés peuvent aussi être fournis comme string.
        if (is_string($subject)) {
            $permission = PermissionEnum::tryFrom($subject);
            if ($permission !== null && $user instanceof User) {
                return $this->permissions->has($user, $permission);
            }
        }

        // On reconstruit l’enum de groupe si nécessaire.
        $group = is_string($subject) ? RoleGroupEnum::from($subject) : $subject;

        if ($user instanceof User) {
            $permission = PermissionEnum::tryFrom($group->value);
            if ($permission !== null) {
                return $this->permissions->has($user, $permission);
            }
        }

        $allowedRoles = $group->roles();
        $userRoles = $token->getRoleNames();

        return !empty(array_intersect($allowedRoles, $userRoles));
    }
}
